<?php

return [

    /*
    |--------------------------------------------------------------------------
    | درگاه پرداخت (Pluggable payment drivers)
    |--------------------------------------------------------------------------
    |
    | Which payment driver App\Services\Payment\PaymentManager resolves.
    | Shipped drivers:
    |
    |   "offline"  کارت به کارت / واریز بانکی — shows the bank/card info below
    |              and asks the customer for a tracking code, which puts the
    |              payment in "manual_review" until an admin confirms it.
    |
    | Future online gateways (Zarinpal, IDPay, ...) plug in by implementing
    | App\Services\Payment\PaymentDriverInterface and registering the class in
    | PaymentManager::$drivers — no caller has to change. See the interface
    | docblock for the exact contract.
    |
    */

    'driver' => env('PAYMENT_DRIVER', 'offline'),

    // Display currency for all amounts (amounts are stored as integer تومان).
    'currency' => 'تومان',

    /*
    |--------------------------------------------------------------------------
    | اطلاعات پرداخت آفلاین (کارت به کارت)
    |--------------------------------------------------------------------------
    */

    'offline' => [
        'bank_name' => env('PAYMENT_BANK_NAME', 'بانک ملی ایران'),
        'card_number' => env('PAYMENT_CARD_NUMBER', '6037-9970-0000-0000'),
        'iban' => env('PAYMENT_IBAN', 'IR00 0000 0000 0000 0000 0000 00'),
        'holder_name' => env('PAYMENT_HOLDER_NAME', 'گروه رسانه‌ای صبح ساحل'),
        // Extra free-form note shown under the instructions (optional).
        'note' => env('PAYMENT_OFFLINE_NOTE', 'پس از واریز، کد رهگیری یا چهار رقم آخر شماره پیگیری تراکنش را در فرم زیر ثبت کنید تا پرداخت شما توسط همکاران ما بررسی و تایید شود.'),
    ],

    /*
    |--------------------------------------------------------------------------
    | اشتراک دیجیتال نشریات (Archive PDF gating)
    |--------------------------------------------------------------------------
    |
    | When true, downloading/viewing archive PDFs requires an active
    | subscription access code (اشتراک دیجیتال). Default false so the current
    | free-archive behavior is unchanged until the business enables it.
    |
    */

    'gate_archive' => env('PAYMENT_GATE_ARCHIVE', false),

    /*
    |--------------------------------------------------------------------------
    | پیامک کد اشتراک
    |--------------------------------------------------------------------------
    |
    | When enabled (and a pattern id is configured on the SMS panel), the
    | subscription access code is SMS'd to the customer on activation via
    | ModirSmsHelper. Always guarded — failures never block activation.
    |
    */

    'sms' => [
        'enabled' => (bool) env('PAYMENT_SMS_ENABLED', false),
        // ippanel pattern id with a single variable named "code".
        'subscription_pattern_id' => env('PAYMENT_SMS_SUBSCRIPTION_PATTERN', ''),
    ],
];
