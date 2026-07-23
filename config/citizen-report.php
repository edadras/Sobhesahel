<?php

return [

    /*
    |--------------------------------------------------------------------------
    | شهروند خبرنگار — Citizen Journalist Settings
    |--------------------------------------------------------------------------
    |
    | Settings for the public /citizen-report submission form and for the
    | editorial review workflow (CitizenReportResource).
    |
    */

    // Master switch for the public submission form.
    'enabled' => true,

    // Per-IP rate limit: at most `max_per_window` reports per `window_minutes`.
    'rate_limit' => [
        'max_per_window' => 3,
        'window_minutes' => 60,
    ],

    // File upload caps. Photos are jpg/png; a single mp4 video is allowed.
    // Sizes are in kilobytes (Laravel's "max" file rule unit).
    'files' => [
        'max_files' => 4,          // total attachments per report (photos + video)
        'max_photos' => 4,         // photos allowed when no video is attached
        'photo_max_kb' => 4096,    // 4 MB per photo
        'video_max_kb' => 30720,   // 30 MB for the single mp4 video
        'directory' => 'citizen-reports', // on the "public" disk
    ],

    // Optional SMS acknowledgment to the submitter's mobile after a report is
    // stored. ModirSmsHelper only exposes pattern-based sending, so a pattern
    // registered with the SMS provider is required; leave the pattern empty
    // (or the toggle off) to skip SMS entirely. Failures are swallowed.
    'sms_enabled' => env('CITIZEN_REPORT_SMS_ENABLED', false),
    'sms_pattern_id' => env('CITIZEN_REPORT_SMS_PATTERN_ID'),
];
