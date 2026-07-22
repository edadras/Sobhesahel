<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Comment Anti-Spam Settings
    |--------------------------------------------------------------------------
    |
    | Settings used by App\Services\CommentSpamGuard to filter incoming
    | comments submitted from the public site.
    |
    */

    // Minimum number of seconds between the comment form being rendered
    // and the comment being submitted. Faster submissions are treated as bots.
    'min_seconds' => 5,

    // Per-IP rate limit: at most `max_per_window` comments per `window_minutes`.
    'rate_limit' => [
        'max_per_window' => 5,
        'window_minutes' => 10,
    ],

    // Maximum number of links (URLs) allowed inside a comment text.
    'max_links' => 2,

    // Comments containing any of these words are stored with status
    // "rejected" and a spam reason, and never shown publicly.
    'blocked_words' => [
        // تبلیغات و اسپم رایج
        'خرید فالوور',
        'فروش فالوور',
        'خرید لایک',
        'افزایش فالوور',
        'کسب درآمد میلیونی',
        'درآمد تضمینی',
        'سود تضمینی',
        'شرط بندی',
        'شرط‌بندی',
        'سایت شرط',
        'پیش بینی فوتبال انفجار',
        'بازی انفجار',
        'کازینو',
        'پوکر آنلاین',
        'قمار',
        'وام فوری',
        'ویزای تضمینی',
        'قرص لاغری',
        'داروی تقویت',
        'بیت کوین رایگان',
        'ارز دیجیتال رایگان',
        // توهین‌آمیز
        'عوضی',
        'بی شرف',
        'بی‌شرف',
        'حرومزاده',
        'حرامزاده',
        'کثافت',
        'آشغال عوضی',
    ],
];
