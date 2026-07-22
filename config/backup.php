<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Automatic Backups (WP-14)
    |--------------------------------------------------------------------------
    |
    | Configuration for the dependency-free backup system driven by the
    | `php artisan app:backup` command (see App\Services\BackupService).
    | Backups are written to storage/app/backups/{db,files}.
    |
    */

    // Master switch: when false the scheduled backups are skipped entirely.
    'enabled' => env('BACKUP_ENABLED', true),

    // Root directory where backup archives are stored.
    'path' => storage_path('app/backups'),

    /*
    |--------------------------------------------------------------------------
    | Database dump (mysqldump)
    |--------------------------------------------------------------------------
    */
    'mysqldump' => [
        // Absolute path to the mysqldump binary if it is not on $PATH.
        'binary_path' => env('BACKUP_MYSQLDUMP_PATH', 'mysqldump'),

        // Extra CLI arguments appended to every dump invocation.
        'extra_args' => [
            '--single-transaction',
            '--quick',
            '--skip-lock-tables',
            '--no-tablespaces',
            '--default-character-set=utf8mb4',
        ],

        // Maximum seconds a dump may run before being aborted.
        'timeout' => (int) env('BACKUP_DB_TIMEOUT', 600),
    ],

    /*
    |--------------------------------------------------------------------------
    | Files backup
    |--------------------------------------------------------------------------
    |
    | Directories zipped by the files backup. These are the upload roots used
    | by Filament FileUpload fields ("public" and "media" disks). The backups
    | directory itself is always excluded automatically.
    |
    */
    'files' => [
        'include' => [
            storage_path('app/public'),
            storage_path('app/media'),
        ],

        // Path prefixes (absolute) to skip while zipping.
        'exclude' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Retention
    |--------------------------------------------------------------------------
    |
    | How many backup archives to keep per type. Older archives are pruned
    | after each successful run.
    |
    */
    'retention' => [
        'db' => (int) env('BACKUP_KEEP_DB', 7),
        'files' => (int) env('BACKUP_KEEP_FILES', 7),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications (placeholder)
    |--------------------------------------------------------------------------
    |
    | Reserved for future use: e-mail address that should receive a report
    | when a backup fails. Not wired to a mailer yet.
    |
    */
    'notification_email' => env('BACKUP_NOTIFICATION_EMAIL'),

];
