<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Member\Concerns\MemberApiResponses;
use App\Http\Resources\Api\Member\MemberResource;
use App\Models\Member;
use App\Services\LoginSecurityService;
use App\Services\Member\MemberOtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * احراز هویت اپ موبایل — توکن Sanctum روی مدل Member.
 *
 * منطق OTP از MemberOtpService و محدودیت نرخ/قفل از LoginSecurityService
 * (همان سرویس‌های فلو وب، با پیشوند member:) بازاستفاده می‌شود — بدون منطق موازی.
 */
class AuthController extends Controller
{
    use MemberApiResponses;

    public function __construct(
        protected MemberOtpService $otp,
        protected LoginSecurityService $security,
    ) {
    }

    /**
     * POST /auth/otp/request — ارسال کد پیامکی.
     */
    public function requestOtp(Request $request)
    {
        if ($this->security->isIpBlocked($request->ip())) {
            return $this->fail('دسترسی شما موقتاً محدود شده است؛ لطفاً بعداً دوباره تلاش کنید.', 429);
        }

        $mobile = Member::normalizeMobile((string) $request->input('mobile'));

        if (! Member::isValidIranianMobile($mobile)) {
            return $this->fail('شماره موبایل واردشده معتبر نیست. نمونه: ۰۹۱۲۳۴۵۶۷۸۹', 422, [
                'mobile' => ['شماره موبایل واردشده معتبر نیست.'],
            ]);
        }

        // A deactivated member may not request codes.
        try {
            $existing = Member::query()->where('mobile', $mobile)->first();

            if ($existing && $existing->is_active === false) {
                return $this->fail('حساب کاربری شما غیرفعال شده است. برای پیگیری با پشتیبانی تماس بگیرید.', 403);
            }
        } catch (\Throwable) {
            // Never block on a lookup failure.
        }

        [$ok, $message] = $this->otp->requestOtp($mobile, $request->ip());

        if (! $ok) {
            return $this->fail($message, 429, ['mobile' => [$message]]);
        }

        $ttlMinutes = max(1, (int) config('login-security.otp.code_ttl_minutes', 5));

        return $this->data([
            'sent' => true,
            'expires_in' => $ttlMinutes * 60,
            'message' => $message,
        ]);
    }

    /**
     * POST /auth/otp/verify — تأیید کد، ورود یا ثبت‌نام خودکار، صدور توکن.
     */
    public function verifyOtp(Request $request)
    {
        if ($this->security->isIpBlocked($request->ip())) {
            return $this->fail('دسترسی شما موقتاً محدود شده است؛ لطفاً بعداً دوباره تلاش کنید.', 429);
        }

        $mobile = Member::normalizeMobile((string) $request->input('mobile'));
        $code = trim(Member::normalizeDigits((string) $request->input('code')));

        if (! Member::isValidIranianMobile($mobile)) {
            return $this->fail('شماره موبایل واردشده معتبر نیست.', 422, [
                'mobile' => ['شماره موبایل واردشده معتبر نیست.'],
            ]);
        }

        if ($code === '') {
            return $this->fail('لطفاً کد تأیید پیامک‌شده را وارد کنید.', 422, [
                'code' => ['کد تأیید را وارد کنید.'],
            ]);
        }

        if (! $this->otp->verifyOtp($mobile, $code)) {
            $this->security->recordFailure('member:' . $mobile, $request->ip());

            return $this->fail('کد واردشده صحیح نیست یا منقضی شده است.', 422, [
                'code' => ['کد واردشده صحیح نیست یا منقضی شده است.'],
            ]);
        }

        // OTP verified → login OR auto-register a member with this mobile.
        $member = Member::query()->firstOrCreate(
            ['mobile' => $mobile],
            ['mobile_verified_at' => now(), 'locale' => 'fa'],
        );

        $isNew = (bool) $member->wasRecentlyCreated;

        if ($member->is_active === false) {
            return $this->fail('حساب کاربری شما غیرفعال شده است. برای پیگیری با پشتیبانی تماس بگیرید.', 403);
        }

        $member->forceFill([
            'mobile_verified_at' => $member->mobile_verified_at ?? now(),
            'last_login_at' => now(),
        ])->save();

        $this->security->clearFailures('member:' . $mobile, $request->ip());

        return $this->data([
            'token' => $this->issueToken($member),
            'member' => (new MemberResource($member))->toArray($request),
            'is_new_member' => $isNew,
        ]);
    }

    /**
     * POST /auth/password/login — ورود با ایمیل و رمز.
     */
    public function passwordLogin(Request $request)
    {
        if ($this->security->isIpBlocked($request->ip())) {
            return $this->fail('دسترسی شما موقتاً محدود شده است؛ لطفاً بعداً دوباره تلاش کنید.', 429);
        }

        $email = mb_strtolower(trim((string) $request->input('email')));
        $password = (string) $request->input('password');

        if ($email === '' || $password === '') {
            return $this->fail('ایمیل و رمز عبور را وارد کنید.', 422, [
                'email' => ['ایمیل و رمز عبور را وارد کنید.'],
            ]);
        }

        $secUsername = 'member:' . $email;

        if ($this->security->isLocked($secUsername)) {
            $minutes = $this->security->lockedRemainingMinutes($secUsername);

            return $this->fail('حساب به دلیل تلاش‌های ناموفق موقتاً قفل شده است؛ حدود ' . $minutes . ' دقیقه دیگر دوباره تلاش کنید.', 429);
        }

        $member = Member::query()->where('email', $email)->first();

        if (! $member || ! $member->hasPassword() || ! Hash::check($password, $member->password)) {
            $this->security->recordFailure($secUsername, $request->ip());

            return $this->fail('ایمیل یا رمز عبور صحیح نیست. اگر رمزی تعیین نکرده‌اید از ورود با موبایل استفاده کنید.', 422, [
                'email' => ['ایمیل یا رمز عبور صحیح نیست.'],
            ]);
        }

        if ($member->is_active === false) {
            return $this->fail('حساب کاربری شما غیرفعال شده است. برای پیگیری با پشتیبانی تماس بگیرید.', 403);
        }

        $member->forceFill(['last_login_at' => now()])->save();
        $this->security->clearFailures($secUsername, $request->ip());

        return $this->data([
            'token' => $this->issueToken($member),
            'member' => (new MemberResource($member))->toArray($request),
        ]);
    }

    /**
     * POST /auth/logout — باطل‌کردن توکن جاری.
     */
    public function logout(Request $request)
    {
        try {
            /** @var Member $member */
            $member = $request->user();
            $token = $member?->currentAccessToken();

            if ($token !== null && method_exists($token, 'delete')) {
                $token->delete();
            }
        } catch (\Throwable) {
            // Idempotent logout — always report success.
        }

        return $this->data(['ok' => true]);
    }

    /**
     * GET /auth/me — عضو جاری.
     */
    public function me(Request $request)
    {
        /** @var Member $member */
        $member = $request->user();

        return $this->data((new MemberResource($member))->toArray($request));
    }

    /**
     * صدور توکن Sanctum. نام توکن از User-Agent مشتق می‌شود.
     */
    protected function issueToken(Member $member): string
    {
        $name = 'mobile-app';

        try {
            return $member->createToken($name)->plainTextToken;
        } catch (\Throwable) {
            return '';
        }
    }
}
