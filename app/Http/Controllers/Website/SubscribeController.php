<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\Payment\PaymentManager;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * اشتراک ویژه — plan cards, order form, offline payment flow, and the
 * archive access-code unlock (اشتراک دیجیتال نشریات).
 */
class SubscribeController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::active()
            ->orderBy('sort')
            ->orderBy('price')
            ->get();

        $brand = setting('general.fa_brand_name') ?? 'گروه رسانه‌ای صبح‌ساحل';

        $website_title = 'اشتراک ویژه | ' . $brand;

        $seo = [
            'title' => 'اشتراک ویژه',
            'description' => 'خرید اشتراک ویژه صبح ساحل و دسترسی به نسخه دیجیتال نشریات و آرشیو PDF روزنامه',
            'type' => 'website',
            'url' => route('website.rtl.subscribe'),
        ];

        return view('website.rtl.subscribe', compact('plans', 'website_title', 'seo'));
    }

    public function store(Request $request, PaymentManager $manager)
    {
        // Honeypot: real users never fill this hidden field.
        if (filled($request->get('website'))) {
            return redirect()->route('website.rtl.subscribe');
        }

        $validated = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'name' => 'required|string|max:120',
            'mobile' => 'required|string|max:20|regex:/^09[0-9]{9}$/',
            'email' => 'nullable|email|max:190',
        ], [
            'plan_id.required' => 'انتخاب پلن اشتراک الزامی است',
            'plan_id.exists' => 'پلن انتخاب شده معتبر نیست',
            'name.required' => 'وارد کردن نام الزامی است',
            'mobile.required' => 'وارد کردن شماره موبایل الزامی است',
            'mobile.regex' => 'شماره موبایل باید با ۰۹ شروع شود و ۱۱ رقم باشد',
            'email.email' => 'ایمیل وارد شده معتبر نیست',
        ]);

        $plan = SubscriptionPlan::active()->findOrFail($validated['plan_id']);

        $subscription = Subscription::create([
            'mobile' => $validated['mobile'],
            'email' => $validated['email'] ?? null,
            'plan_id' => $plan->id,
            'status' => Subscription::STATUS_PENDING,
        ]);

        [$payment] = $manager->start($subscription, (int) $plan->price, $validated['name'], $validated['mobile']);

        $subscription->forceFill(['payment_id' => $payment->id])->save();

        return redirect()->route('website.rtl.subscribe.pay', ['token' => $payment->token]);
    }

    /**
     * صفحه پرداخت اشتراک — keyed by the Payment token.
     */
    public function pay(string $token, PaymentManager $manager)
    {
        $payment = Payment::where('token', $token)
            ->where('payable_type', Subscription::class)
            ->firstOrFail();

        /** @var Subscription $subscription */
        $subscription = $payment->payable;

        $init = $payment->status === Payment::STATUS_PENDING
            ? $manager->instructions($payment)
            : null;

        $brand = setting('general.fa_brand_name') ?? 'گروه رسانه‌ای صبح‌ساحل';
        $website_title = 'پرداخت اشتراک ویژه | ' . $brand;
        $seo = [
            'title' => 'پرداخت اشتراک ویژه',
            'description' => 'پرداخت هزینه اشتراک ویژه صبح ساحل',
            'type' => 'website',
            'url' => route('website.rtl.subscribe.pay', ['token' => $token]),
        ];

        return view('website.rtl.subscribe_pay', compact('payment', 'subscription', 'init', 'website_title', 'seo'));
    }

    /**
     * ثبت کد رهگیری واریز اشتراک (پرداخت آفلاین).
     */
    public function submitPayment(string $token, Request $request, PaymentManager $manager)
    {
        $payment = Payment::where('token', $token)
            ->where('payable_type', Subscription::class)
            ->firstOrFail();

        if ($payment->isPaid()) {
            return redirect()->route('website.rtl.subscribe.pay', ['token' => $token]);
        }

        $request->validate([
            'ref_code' => 'required|string|max:100',
        ], [
            'ref_code.required' => 'وارد کردن کد رهگیری واریز الزامی است',
        ]);

        $result = $manager->verify($payment, ['ref_code' => $request->get('ref_code')]);

        // Online gateways activate immediately; the offline driver leaves the
        // subscription pending until an admin confirms from the panel.
        if ($result['success'] && $result['status'] === Payment::STATUS_PAID) {
            $payment->payable?->activate();
        }

        return redirect()
            ->route('website.rtl.subscribe.pay', ['token' => $token])
            ->with('payment_message', $result['message']);
    }

    /**
     * ورود کد اشتراک برای دانلود نسخه دیجیتال نشریات (PDF آرشیو).
     */
    public function archiveAccess(Request $request)
    {
        $request->validate([
            'access_code' => 'required|string|max:32',
        ], [
            'access_code.required' => 'وارد کردن کد اشتراک الزامی است',
        ]);

        $subscription = Subscription::findActiveByCode($request->get('access_code'));

        if ($subscription === null) {
            return redirect()
                ->back()
                ->withInput()
                ->with('archive_access_error', 'کد اشتراک معتبر نیست یا اشتراک شما منقضی شده است');
        }

        session(['subscription_access_code' => $subscription->access_code]);

        // Only ever redirect inside this site.
        $redirect = (string) $request->get('redirect_to', '');

        if ($redirect === '' || ! Str::startsWith($redirect, url('/'))) {
            $redirect = route('website.rtl.archive');
        }

        return redirect()->to($redirect);
    }
}
