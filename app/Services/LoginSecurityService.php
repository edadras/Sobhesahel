<?php

namespace App\Services;

use App\Helpers\ModirSmsHelper;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Cache-based login protection: failed-attempt counters (per username+IP,
 * per username and per IP), conditional-captcha threshold, account lockout,
 * automatic IP blocking and the SMS one-time-password flow.
 *
 * Everything is stored in the cache, so no migrations are required and all
 * counters expire on their own.
 */
class LoginSecurityService
{
    protected const PREFIX = 'login_sec';

    /* ----------------------------------------------------------------- *
     *  Failed attempt tracking
     * ----------------------------------------------------------------- */

    /**
     * Record a failed login attempt for the given username and IP.
     * Locks the account / auto-blocks the IP when thresholds are reached.
     */
    public function recordFailure(?string $username, ?string $ip): void
    {
        $username = $this->normalizeUsername((string) $username);
        $ip = (string) $ip;
        $window = now()->addMinutes($this->failureWindowMinutes());

        if ($username !== '' && $ip !== '') {
            $this->incrementCounter($this->pairKey($username, $ip), $window);
        }

        if ($username !== '') {
            $userFailures = $this->incrementCounter($this->userKey($username), $window);

            $maxAttempts = max(1, (int) config('login-security.lockout.max_attempts', 15));

            if ($userFailures >= $maxAttempts && ! $this->isLocked($username)) {
                $this->lockAccount($username, $ip, $userFailures);
            }
        }

        if ($ip !== '') {
            $ipFailures = $this->incrementCounter($this->ipKey($ip), $window);

            $maxIpFailures = max(1, (int) config('login-security.ip.auto_block.max_failures', 30));

            if ($ipFailures >= $maxIpFailures && ! Cache::has($this->ipBlockKey($ip))) {
                $this->blockIp($ip, $ipFailures);
            }
        }
    }

    /**
     * Reset all failure counters after a successful login.
     */
    public function clearFailures(?string $username, ?string $ip): void
    {
        $username = $this->normalizeUsername((string) $username);
        $ip = (string) $ip;

        if ($username !== '') {
            Cache::forget($this->userKey($username));
            Cache::forget($this->lockKey($username));

            if ($ip !== '') {
                Cache::forget($this->pairKey($username, $ip));
            }
        }

        if ($ip !== '') {
            Cache::forget($this->ipKey($ip));
        }
    }

    /**
     * Should the login form require a captcha for this username / IP?
     */
    public function captchaRequired(?string $username, ?string $ip): bool
    {
        $threshold = max(1, (int) config('login-security.captcha_threshold', 3));
        $username = $this->normalizeUsername((string) $username);
        $ip = (string) $ip;

        $pairCount = ($username !== '' && $ip !== '')
            ? (int) Cache::get($this->pairKey($username, $ip), 0)
            : 0;

        $ipCount = $ip !== '' ? (int) Cache::get($this->ipKey($ip), 0) : 0;

        return max($pairCount, $ipCount) >= $threshold;
    }

    /* ----------------------------------------------------------------- *
     *  Account lockout
     * ----------------------------------------------------------------- */

    public function isLocked(?string $username): bool
    {
        return $this->lockedRemainingMinutes($username) > 0;
    }

    /**
     * Remaining lock time in minutes (0 when the account is not locked).
     */
    public function lockedRemainingMinutes(?string $username): int
    {
        $username = $this->normalizeUsername((string) $username);

        if ($username === '') {
            return 0;
        }

        $lockedUntil = (int) Cache::get($this->lockKey($username), 0);

        if ($lockedUntil <= time()) {
            return 0;
        }

        return max(1, (int) ceil(($lockedUntil - time()) / 60));
    }

    protected function lockAccount(string $username, string $ip, int $failures): void
    {
        $minutes = max(1, (int) config('login-security.lockout.duration_minutes', 60));

        Cache::put(
            $this->lockKey($username),
            now()->addMinutes($minutes)->getTimestamp(),
            now()->addMinutes($minutes)
        );

        Log::warning('Login security: account locked after repeated failures.', [
            'username' => $username,
            'ip' => $ip,
            'failures' => $failures,
            'locked_minutes' => $minutes,
        ]);
    }

    /* ----------------------------------------------------------------- *
     *  IP blocking
     * ----------------------------------------------------------------- */

    public function isIpBlocked(?string $ip): bool
    {
        $ip = (string) $ip;

        if ($ip === '') {
            return false;
        }

        if (in_array($ip, (array) config('login-security.ip.blocked_ips', []), true)) {
            return true;
        }

        return Cache::has($this->ipBlockKey($ip));
    }

    protected function blockIp(string $ip, int $failures): void
    {
        $minutes = max(1, (int) config('login-security.ip.auto_block.duration_minutes', 60));

        Cache::put($this->ipBlockKey($ip), time(), now()->addMinutes($minutes));

        Log::warning('Login security: IP auto-blocked after repeated failures across accounts.', [
            'ip' => $ip,
            'failures' => $failures,
            'blocked_minutes' => $minutes,
        ]);
    }

    /* ----------------------------------------------------------------- *
     *  One-time password (SMS) login
     * ----------------------------------------------------------------- */

    /**
     * Generate and send an OTP code to the given mobile number.
     *
     * @return array{0: bool, 1: string} [success, Persian message]
     */
    public function requestOtp(?string $mobile): array
    {
        $mobile = $this->normalizeMobile((string) $mobile);

        if ($mobile === '') {
            return [false, 'لطفاً شماره موبایل خود را وارد کنید.'];
        }

        $user = User::query()->where('mobile', $mobile)->first();

        if (! $user) {
            return [false, 'کاربری با این شماره موبایل یافت نشد.'];
        }

        $interval = max(30, (int) config('login-security.otp.sms_interval_seconds', 120));

        // Cache::add is atomic: it fails while a previous OTP throttle exists.
        if (! Cache::add($this->otpThrottleKey($mobile), time(), $interval)) {
            return [false, 'کد ورود به‌تازگی ارسال شده است؛ لطفاً حدود دو دقیقه دیگر دوباره تلاش کنید.'];
        }

        $length = max(4, (int) config('login-security.otp.code_length', 6));
        $code = str_pad((string) random_int(0, (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);
        $ttl = max(1, (int) config('login-security.otp.code_ttl_minutes', 5));

        // Only the hash of the code is stored.
        Cache::put($this->otpKey($mobile), Hash::make($code), now()->addMinutes($ttl));

        if (! ModirSmsHelper::send_otp($mobile, $code)) {
            Cache::forget($this->otpKey($mobile));

            return [false, 'ارسال پیامک با خطا مواجه شد؛ لطفاً بعداً دوباره تلاش کنید.'];
        }

        return [true, 'کد ورود پیامک شد و تا ' . $ttl . ' دقیقه معتبر است.'];
    }

    /**
     * Verify an OTP code. Returns the matching user on success, null otherwise.
     * The code is single-use: it is forgotten as soon as it matches.
     */
    public function verifyOtp(?string $mobile, ?string $code): ?User
    {
        $mobile = $this->normalizeMobile((string) $mobile);
        $code = trim($this->normalizeDigits((string) $code));

        if ($mobile === '' || $code === '') {
            return null;
        }

        $hash = Cache::get($this->otpKey($mobile));

        if (! is_string($hash) || ! Hash::check($code, $hash)) {
            return null;
        }

        Cache::forget($this->otpKey($mobile));
        Cache::forget($this->otpThrottleKey($mobile));

        return User::query()->where('mobile', $mobile)->first();
    }

    /* ----------------------------------------------------------------- *
     *  Helpers
     * ----------------------------------------------------------------- */

    /**
     * Increment a windowed counter and return the new value.
     */
    protected function incrementCounter(string $key, \DateTimeInterface $expiresAt): int
    {
        // Cache::add only writes when the key is missing, so the window TTL
        // is anchored to the first failure and never extended afterwards.
        Cache::add($key, 0, $expiresAt);

        return (int) Cache::increment($key);
    }

    protected function failureWindowMinutes(): int
    {
        return max(1, (int) config('login-security.failure_window_minutes', 60));
    }

    protected function normalizeUsername(string $username): string
    {
        return mb_strtolower(trim($username));
    }

    /**
     * Normalize a mobile number: convert Persian/Arabic digits and strip
     * everything except digits (keeping an optional leading +).
     */
    public function normalizeMobile(string $mobile): string
    {
        $mobile = $this->normalizeDigits(trim($mobile));
        $plus = str_starts_with($mobile, '+') ? '+' : '';

        return $plus . preg_replace('/\D+/', '', $mobile);
    }

    protected function normalizeDigits(string $value): string
    {
        return str_replace(
            ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'],
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            $value
        );
    }

    protected function pairKey(string $username, string $ip): string
    {
        return self::PREFIX . ':fail_pair:' . sha1($username . '|' . $ip);
    }

    protected function userKey(string $username): string
    {
        return self::PREFIX . ':fail_user:' . sha1($username);
    }

    protected function ipKey(string $ip): string
    {
        return self::PREFIX . ':fail_ip:' . sha1($ip);
    }

    protected function lockKey(string $username): string
    {
        return self::PREFIX . ':lock:' . sha1($username);
    }

    protected function ipBlockKey(string $ip): string
    {
        return self::PREFIX . ':ip_block:' . sha1($ip);
    }

    protected function otpKey(string $mobile): string
    {
        return self::PREFIX . ':otp:' . sha1($mobile);
    }

    protected function otpThrottleKey(string $mobile): string
    {
        return self::PREFIX . ':otp_throttle:' . sha1($mobile);
    }
}
