<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Services\LoginSecurityService;
use App\Services\Member\MemberOtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * ورود / ثبت‌نام اعضا — OTP موبایلی (مسیر اصلی) + ایمیل و رمز (اختیاری).
 *
 * OTP verification doubles as registration: a verified mobile that has no
 * member row yet is auto-registered with mobile_verified_at set.
 *
 * Login security intentionally reuses the existing LoginSecurityService (ت۳):
 * IP blocks are shared, while member usernames are namespaced with the
 * `member:` prefix before being handed to the service so member lock/failure
 * counters never collide with admin ones.
 */
class MemberAuthController extends Controller
{
    public function __construct(
        protected MemberOtpService $otp,
        protected LoginSecurityService $security,
    ) {
    }

    /**
     * صفحه ورود/ثبت‌نام (member/login).
     */
    public function showLogin(Request $request)
    {
        return view('member.auth.login', [
            'otpMobile' => $request->session()->get('member_otp_mobile'),
            'otpTtl' => max(1, (int) config('login-security.otp.code_ttl_minutes', 5)),
        ]);
    }

    /**
     * ارسال کد یک‌بارمصرف (POST member/login/otp).
     */
    public function sendOtp(Request $request)
    {
        if ($this->security->isIpBlocked($request->ip())) {
            return back()->withErrors(['mobile' => 'دسترسی شما موقتاً محدود شده است؛ لطفاً بعداً دوباره تلاش کنید.']);
        }

        $mobile = Member::normalizeMobile((string) $request->input('mobile'));

        if (! Member::isValidIranianMobile($mobile)) {
            return back()
                ->withInput(['mobile' => $request->input('mobile')])
                ->withErrors(['mobile' => 'شماره موبایل واردشده معتبر نیست. نمونه: ۰۹۱۲۳۴۵۶۷۸۹']);
        }

        // An existing but deactivated member may not request codes.
        $member = Member::query()->where('mobile', $mobile)->first();

        if ($member && $member->is_active === false) {
            return back()->withErrors(['mobile' => 'حساب کاربری شما غیرفعال شده است. برای پیگیری با پشتیبانی تماس بگیرید.']);
        }

        [$ok, $message] = $this->otp->requestOtp($mobile, $request->ip());

        if (! $ok) {
            return back()
                ->withInput(['mobile' => $request->input('mobile')])
                ->withErrors(['mobile' => $message]);
        }

        $request->session()->put('member_otp_mobile', $mobile);

        return redirect()->route('member.login')->with('status', $message);
    }

    /**
     * تأیید کد و ورود / ثبت‌نام خودکار (POST member/login/otp/verify).
     */
    public function verifyOtp(Request $request)
    {
        if ($this->security->isIpBlocked($request->ip())) {
            return back()->withErrors(['code' => 'دسترسی شما موقتاً محدود شده است؛ لطفاً بعداً دوباره تلاش کنید.']);
        }

        $mobile = Member::normalizeMobile(
            (string) ($request->input('mobile') ?: $request->session()->get('member_otp_mobile'))
        );
        $code = trim(Member::normalizeDigits((string) $request->input('code')));

        if (! Member::isValidIranianMobile($mobile)) {
            $request->session()->forget('member_otp_mobile');

            return redirect()->route('member.login')
                ->withErrors(['mobile' => 'لطفاً ابتدا شماره موبایل خود را وارد کنید.']);
        }

        if ($code === '') {
            return back()->withErrors(['code' => 'لطفاً کد تأیید پیامک‌شده را وارد کنید.']);
        }

        if (! $this->otp->verifyOtp($mobile, $code)) {
            $this->security->recordFailure('member:' . $mobile, $request->ip());

            return back()->withErrors(['code' => 'کد واردشده صحیح نیست یا منقضی شده است.']);
        }

        // OTP verified → login OR auto-register a new member with this mobile.
        $member = Member::query()->firstOrCreate(
            ['mobile' => $mobile],
            ['mobile_verified_at' => now(), 'locale' => 'fa'],
        );

        if ($member->is_active === false) {
            return redirect()->route('member.login')
                ->withErrors(['mobile' => 'حساب کاربری شما غیرفعال شده است. برای پیگیری با پشتیبانی تماس بگیرید.']);
        }

        $member->forceFill([
            'mobile_verified_at' => $member->mobile_verified_at ?? now(),
            'last_login_at' => now(),
        ])->save();

        $this->security->clearFailures('member:' . $mobile, $request->ip());
        $request->session()->forget('member_otp_mobile');

        Auth::guard('member')->login($member, remember: true);
        $request->session()->regenerate();

        return redirect()->intended(route('member.dashboard'))
            ->with('status', 'خوش آمدید! با موفقیت وارد شدید.');
    }

    /**
     * بازگشت به مرحله شماره موبایل («تغییر شماره موبایل»).
     */
    public function resetOtp(Request $request)
    {
        $request->session()->forget('member_otp_mobile');

        return redirect()->route('member.login');
    }

    /**
     * ورود با ایمیل و رمز (POST member/login/password) — only for members
     * who have set a password in settings.
     */
    public function loginWithPassword(Request $request)
    {
        if ($this->security->isIpBlocked($request->ip())) {
            return back()->withErrors(['email' => 'دسترسی شما موقتاً محدود شده است؛ لطفاً بعداً دوباره تلاش کنید.'])
                ->withInput(['email' => $request->input('email'), 'tab' => 'email']);
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'لطفاً ایمیل خود را وارد کنید.',
            'email.email' => 'قالب ایمیل واردشده صحیح نیست.',
            'password.required' => 'لطفاً رمز عبور را وارد کنید.',
        ]);

        $email = mb_strtolower(trim($validated['email']));
        $secUsername = 'member:' . $email;

        if ($this->security->isLocked($secUsername)) {
            $minutes = $this->security->lockedRemainingMinutes($secUsername);

            return back()
                ->withInput(['email' => $request->input('email'), 'tab' => 'email'])
                ->withErrors(['email' => 'حساب به دلیل تلاش‌های ناموفق موقتاً قفل شده است؛ حدود ' . $minutes . ' دقیقه دیگر دوباره تلاش کنید.']);
        }

        $member = Member::query()->where('email', $email)->first();

        if (! $member || ! $member->hasPassword() || ! Hash::check($validated['password'], $member->password)) {
            $this->security->recordFailure($secUsername, $request->ip());

            return back()
                ->withInput(['email' => $request->input('email'), 'tab' => 'email'])
                ->withErrors(['email' => 'ایمیل یا رمز عبور صحیح نیست. اگر رمزی تعیین نکرده‌اید از ورود با موبایل استفاده کنید.']);
        }

        if ($member->is_active === false) {
            return back()
                ->withInput(['tab' => 'email'])
                ->withErrors(['email' => 'حساب کاربری شما غیرفعال شده است. برای پیگیری با پشتیبانی تماس بگیرید.']);
        }

        $member->forceFill(['last_login_at' => now()])->save();

        $this->security->clearFailures($secUsername, $request->ip());

        Auth::guard('member')->login($member, remember: true);
        $request->session()->regenerate();

        return redirect()->intended(route('member.dashboard'))
            ->with('status', 'خوش آمدید! با موفقیت وارد شدید.');
    }

    /**
     * خروج از حساب (POST member/logout).
     */
    public function logout(Request $request)
    {
        Auth::guard('member')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('member.login')
            ->with('status', 'با موفقیت از حساب خود خارج شدید.');
    }
}
