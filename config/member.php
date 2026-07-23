<?php

return [

    /*
    |--------------------------------------------------------------------------
    | پنل اعضا (باشگاه اعضای صبح ساحل)
    |--------------------------------------------------------------------------
    |
    | Settings for the member area (member/... routes, `member` guard).
    | OTP timing values intentionally reuse config/login-security.php (ت۳)
    | so the member flow and the admin flow stay in sync; only the member
    | -specific caps live here.
    |
    */

    // Per-IP cap on OTP SMS requests (member login) inside the window below.
    'otp' => [
        'ip_max_requests' => (int) env('MEMBER_OTP_IP_MAX_REQUESTS', 10),
        'ip_window_minutes' => (int) env('MEMBER_OTP_IP_WINDOW_MINUTES', 60),

        // Wrong-code attempts allowed per mobile before the code is voided.
        'max_verify_attempts' => (int) env('MEMBER_OTP_MAX_VERIFY_ATTEMPTS', 5),
    ],

    // Avatar upload constraints (member settings page).
    'avatar' => [
        'max_kb' => (int) env('MEMBER_AVATAR_MAX_KB', 2048),
        'mimes' => ['jpg', 'jpeg', 'png', 'webp'],
        'directory' => 'members/avatars',
    ],

    // Hormozgan cities offered in the settings profile form (template list
    // first, then the remaining شهرستان‌های هرمزگان).
    'cities' => [
        'بندرعباس',
        'قشم',
        'کیش',
        'میناب',
        'بندرلنگه',
        'رودان',
        'جاسک',
        'حاجی‌آباد',
        'بستک',
        'بندر خمیر',
        'پارسیان',
        'سیریک',
        'بشاگرد',
        'هرمز',
        'ابوموسی',
        'سایر',
    ],
];
