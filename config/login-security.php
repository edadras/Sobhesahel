<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Login Security (admin panel + site login)
    |--------------------------------------------------------------------------
    |
    | Used by App\Services\LoginSecurityService, the Filament Login page
    | (App\Filament\Pages\Auth\Login) and the BlockLoginIps middleware.
    | All counters are cache-based, so nothing here requires a migration.
    |
    */

    // Failed attempts (per username + IP) before the captcha field becomes
    // required on the login form.
    'captcha_threshold' => (int) env('LOGIN_CAPTCHA_THRESHOLD', 3),

    // Sliding window (minutes) in which failed attempts are counted.
    'failure_window_minutes' => (int) env('LOGIN_FAILURE_WINDOW_MINUTES', 60),

    // Account lockout: after `max_attempts` failures for the same username,
    // the account is locked for `duration_minutes`.
    'lockout' => [
        'max_attempts' => (int) env('LOGIN_LOCKOUT_ATTEMPTS', 15),
        'duration_minutes' => (int) env('LOGIN_LOCKOUT_MINUTES', 60),
    ],

    'ip' => [
        // Permanently blocked IPs (comma-separated in .env, e.g. "1.2.3.4,5.6.7.8").
        'blocked_ips' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('LOGIN_BLOCKED_IPS', ''))
        ))),

        // Auto-block an IP for `duration_minutes` after `max_failures`
        // failed attempts across ALL accounts within the failure window.
        'auto_block' => [
            'max_failures' => (int) env('LOGIN_IP_MAX_FAILURES', 30),
            'duration_minutes' => (int) env('LOGIN_IP_BLOCK_MINUTES', 60),
        ],
    ],

    // One-time-password (SMS) login.
    'otp' => [
        // Minimum seconds between two OTP SMS for the same mobile number.
        'sms_interval_seconds' => (int) env('LOGIN_OTP_SMS_INTERVAL', 120),
        // Minutes an OTP code stays valid.
        'code_ttl_minutes' => (int) env('LOGIN_OTP_TTL_MINUTES', 5),
        // Number of digits in the generated code.
        'code_length' => 6,
    ],
];
