<?php

namespace App\Services\Member;

use App\Helpers\ModirSmsHelper;
use App\Models\Member;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * ورود/ثبت‌نام اعضا با کد یک‌بارمصرف پیامکی.
 *
 * Deliberately a thin re-use of the existing OTP stack (تداخل ت۳):
 * the SMS goes out through the SAME ModirSmsHelper::send_otp pattern and the
 * timing knobs come from the SAME config/login-security.php, but every cache
 * key is prefixed `member:` so member counters never collide with the admin
 * LoginSecurityService (`login_sec:*`) counters.
 *
 * Everything is cache-based (bcrypt-hashed code, ~5 min TTL) — no migrations.
 */
class MemberOtpService
{
    protected const PREFIX = 'member:otp';

    /**
     * Generate and SMS an OTP code to the given (normalized) mobile.
     * Rate-limited: 1 SMS per sms_interval_seconds per mobile (atomic
     * Cache::add) + a per-IP cap so one IP cannot burn the SMS budget.
     *
     * @return array{0: bool, 1: string} [success, Persian message]
     */
    public function requestOtp(string $mobile, ?string $ip): array
    {
        $mobile = Member::normalizeMobile($mobile);

        if (! Member::isValidIranianMobile($mobile)) {
            return [false, 'شماره موبایل واردشده معتبر نیست. نمونه: ۰۹۱۲۳۴۵۶۷۸۹'];
        }

        // Per-IP cap (member: prefixed — separate from the admin counters).
        $ip = (string) $ip;

        if ($ip !== '') {
            $windowMinutes = max(1, (int) config('member.otp.ip_window_minutes', 60));
            $maxRequests = max(1, (int) config('member.otp.ip_max_requests', 10));

            Cache::add($this->ipKey($ip), 0, now()->addMinutes($windowMinutes));

            if ((int) Cache::increment($this->ipKey($ip)) > $maxRequests) {
                Log::warning('Member OTP: per-IP request cap reached.', ['ip' => $ip]);

                return [false, 'تعداد درخواست‌های شما بیش از حد مجاز است؛ لطفاً بعداً دوباره تلاش کنید.'];
            }
        }

        // 1 SMS per interval per mobile — Cache::add is atomic and fails
        // while a previous throttle key is still alive.
        $interval = max(30, (int) config('login-security.otp.sms_interval_seconds', 120));

        if (! Cache::add($this->throttleKey($mobile), time(), $interval)) {
            return [false, 'کد ورود به‌تازگی ارسال شده است؛ لطفاً حدود دو دقیقه دیگر دوباره تلاش کنید.'];
        }

        $length = max(4, (int) config('login-security.otp.code_length', 6));
        $code = str_pad((string) random_int(0, (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);
        $ttl = max(1, (int) config('login-security.otp.code_ttl_minutes', 5));

        // Only the bcrypt hash of the code is stored.
        Cache::put($this->codeKey($mobile), Hash::make($code), now()->addMinutes($ttl));
        Cache::forget($this->attemptsKey($mobile));

        if (! ModirSmsHelper::send_otp($mobile, $code)) {
            Cache::forget($this->codeKey($mobile));

            return [false, 'ارسال پیامک با خطا مواجه شد؛ لطفاً بعداً دوباره تلاش کنید.'];
        }

        return [true, 'کد ورود پیامک شد و تا ' . $ttl . ' دقیقه معتبر است.'];
    }

    /**
     * Verify an OTP code for the given mobile. Single-use: the code is
     * forgotten as soon as it matches. Wrong attempts are counted and the
     * code is voided after max_verify_attempts failures.
     */
    public function verifyOtp(string $mobile, ?string $code): bool
    {
        $mobile = Member::normalizeMobile($mobile);
        $code = trim(Member::normalizeDigits((string) $code));

        if (! Member::isValidIranianMobile($mobile) || $code === '') {
            return false;
        }

        $hash = Cache::get($this->codeKey($mobile));

        if (! is_string($hash)) {
            return false;
        }

        if (! Hash::check($code, $hash)) {
            $ttl = max(1, (int) config('login-security.otp.code_ttl_minutes', 5));
            Cache::add($this->attemptsKey($mobile), 0, now()->addMinutes($ttl + 5));

            $attempts = (int) Cache::increment($this->attemptsKey($mobile));
            $maxAttempts = max(1, (int) config('member.otp.max_verify_attempts', 5));

            if ($attempts >= $maxAttempts) {
                // Too many wrong guesses: void the code entirely.
                Cache::forget($this->codeKey($mobile));
                Cache::forget($this->attemptsKey($mobile));

                Log::warning('Member OTP: code voided after repeated wrong attempts.', [
                    'mobile' => $mobile,
                ]);
            }

            return false;
        }

        Cache::forget($this->codeKey($mobile));
        Cache::forget($this->throttleKey($mobile));
        Cache::forget($this->attemptsKey($mobile));

        return true;
    }

    /* ----------------------------------------------------------------- *
     *  Cache keys — all `member:` prefixed (ت۳)
     * ----------------------------------------------------------------- */

    protected function codeKey(string $mobile): string
    {
        return self::PREFIX . ':code:' . sha1($mobile);
    }

    protected function throttleKey(string $mobile): string
    {
        return self::PREFIX . ':throttle:' . sha1($mobile);
    }

    protected function attemptsKey(string $mobile): string
    {
        return self::PREFIX . ':attempts:' . sha1($mobile);
    }

    protected function ipKey(string $ip): string
    {
        return self::PREFIX . ':ip:' . sha1($ip);
    }
}
