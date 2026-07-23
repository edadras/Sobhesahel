<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\ReportageOrder;
use App\Services\Payment\PaymentManager;
use Illuminate\Http\Request;

/**
 * رپورتاژ آگهی — public order form + offline payment page.
 */
class ReportageController extends Controller
{
    public function index()
    {
        $brand = setting('general.fa_brand_name') ?? 'گروه رسانه‌ای صبح‌ساحل';

        $website_title = 'رپورتاژ آگهی | ' . $brand;

        $seo = [
            'title' => 'رپورتاژ آگهی',
            'description' => 'سفارش رپورتاژ آگهی در پایگاه خبری صبح ساحل؛ معرفی کسب‌وکار شما به مخاطبان استان هرمزگان',
            'type' => 'website',
            'url' => route('website.rtl.reportage'),
        ];

        return view('website.rtl.reportage', compact('website_title', 'seo'));
    }

    public function store(Request $request)
    {
        // Honeypot: real users never fill this hidden field.
        if (filled($request->get('website'))) {
            return redirect()->route('website.rtl.reportage');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'mobile' => 'required|string|max:20|regex:/^09[0-9]{9}$/',
            'email' => 'nullable|email|max:190',
            'subject' => 'required|string|max:255',
            'brief' => 'required|string|max:5000',
            'link' => 'nullable|url|max:2048',
            'desired_publish_date' => 'nullable|date|after_or_equal:today',
        ], [
            'name.required' => 'وارد کردن نام الزامی است',
            'mobile.required' => 'وارد کردن شماره موبایل الزامی است',
            'mobile.regex' => 'شماره موبایل باید با ۰۹ شروع شود و ۱۱ رقم باشد',
            'email.email' => 'ایمیل وارد شده معتبر نیست',
            'subject.required' => 'وارد کردن موضوع رپورتاژ الزامی است',
            'brief.required' => 'وارد کردن شرح سفارش الزامی است',
            'brief.max' => 'شرح سفارش حداکثر ۵۰۰۰ کاراکتر است',
            'link.url' => 'آدرس لینک معتبر نیست',
            'desired_publish_date.date' => 'تاریخ انتشار پیشنهادی معتبر نیست',
            'desired_publish_date.after_or_equal' => 'تاریخ انتشار پیشنهادی نمی‌تواند در گذشته باشد',
        ]);

        $order = ReportageOrder::create([
            ...$validated,
            'status' => ReportageOrder::STATUS_NEW,
            'ip_address' => $request->ip(),
        ]);

        return redirect()
            ->route('website.rtl.reportage')
            ->with('reportage_success', 'سفارش شما با موفقیت ثبت شد؛ همکاران ما پس از بررسی، قیمت و لینک پرداخت را به شما اطلاع می‌دهند')
            ->with('reportage_pay_url', $order->payUrl());
    }

    /**
     * صفحه پرداخت سفارش — customers land here after the admin sets the price.
     */
    public function pay(string $token, PaymentManager $manager)
    {
        $order = ReportageOrder::where('token', $token)->firstOrFail();

        $payment = null;
        $init = null;

        if ($order->status === ReportageOrder::STATUS_AWAITING_PAYMENT && $order->price > 0) {
            $payment = $order->payment;

            if ($payment === null) {
                [$payment, $init] = $manager->start($order, (int) $order->price, $order->name, $order->mobile);
                $order->forceFill(['payment_id' => $payment->id])->save();
            } else {
                $init = $manager->instructions($payment);
            }
        } elseif ($order->payment_id !== null) {
            $payment = $order->payment;
        }

        $brand = setting('general.fa_brand_name') ?? 'گروه رسانه‌ای صبح‌ساحل';
        $website_title = 'پرداخت رپورتاژ آگهی | ' . $brand;
        $seo = [
            'title' => 'پرداخت رپورتاژ آگهی',
            'description' => 'پرداخت هزینه سفارش رپورتاژ آگهی',
            'type' => 'website',
            'url' => $order->payUrl(),
        ];

        return view('website.rtl.reportage_pay', compact('order', 'payment', 'init', 'website_title', 'seo'));
    }

    /**
     * ثبت کد رهگیری واریز (پرداخت آفلاین).
     */
    public function submitPayment(string $token, Request $request, PaymentManager $manager)
    {
        $order = ReportageOrder::where('token', $token)->firstOrFail();

        $payment = $order->payment;

        if ($payment === null || $payment->isPaid()) {
            return redirect()->to($order->payUrl());
        }

        $request->validate([
            'ref_code' => 'required|string|max:100',
        ], [
            'ref_code.required' => 'وارد کردن کد رهگیری واریز الزامی است',
        ]);

        $result = $manager->verify($payment, ['ref_code' => $request->get('ref_code')]);

        if ($result['success'] && $result['status'] === Payment::STATUS_PAID) {
            $order->forceFill(['status' => ReportageOrder::STATUS_PAID])->save();
        }

        return redirect()
            ->to($order->payUrl())
            ->with('payment_message', $result['message']);
    }
}
