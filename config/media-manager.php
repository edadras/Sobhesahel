<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Allowed roots
    |--------------------------------------------------------------------------
    | Local disks (defined in config/filesystems.php) that the media manager
    | is allowed to browse. Keys are disk names, values are Persian labels.
    | Only "local" driver disks are ever served; anything else is ignored.
    */

    'disks' => [
        'public' => 'فضای عمومی (storage)',
        'media' => 'رسانه‌ها (media)',
    ],

    // Files shown per page in the browser grid.
    'per_page' => 48,

    // Hard cap on recursive filename-search results.
    'search_limit' => 300,

    // Maximum folders offered in the "move to folder" select.
    'folder_select_limit' => 500,

    /*
    |--------------------------------------------------------------------------
    | Uploads
    |--------------------------------------------------------------------------
    */

    'upload' => [
        // Per-file size cap in kilobytes (default 50 MB).
        'max_size_kb' => (int) env('MEDIA_MANAGER_MAX_UPLOAD_KB', 51200),
        // Maximum number of files per upload batch.
        'max_files' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed file types
    |--------------------------------------------------------------------------
    | Extension allowlist is enforced on upload, rename and URL download so a
    | file can never be stored (or renamed to) an executable extension.
    */

    'allowed_extensions' => [
        // images
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'avif', 'bmp', 'ico',
        // audio
        'mp3', 'ogg', 'wav', 'm4a', 'aac',
        // video
        'mp4', 'webm', 'mov', 'mkv', 'mpg', 'mpeg',
        // documents / archives
        'pdf', 'zip', 'rar', 'txt', 'csv',
        'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
    ],

    'allowed_mime_types' => [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
        'image/avif', 'image/bmp', 'image/x-icon', 'image/vnd.microsoft.icon',
        'audio/mpeg', 'audio/mp3', 'audio/ogg', 'audio/wav', 'audio/x-wav',
        'audio/mp4', 'audio/aac', 'audio/x-m4a',
        'video/mp4', 'video/webm', 'video/quicktime', 'video/x-matroska', 'video/mpeg',
        'application/pdf', 'application/zip', 'application/x-zip-compressed',
        'application/x-rar-compressed', 'application/vnd.rar',
        'text/plain', 'text/csv',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    ],

    /*
    |--------------------------------------------------------------------------
    | Download from URL
    |--------------------------------------------------------------------------
    */

    'url_download' => [
        // Request timeout in seconds.
        'timeout' => 20,
        // Size cap in kilobytes (default 30 MB).
        'max_size_kb' => (int) env('MEDIA_MANAGER_URL_MAX_KB', 30720),
        // Content-Type allowlist; null falls back to allowed_mime_types above.
        'allowed_mime_types' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Webpage image harvester
    |--------------------------------------------------------------------------
    */

    'harvest' => [
        // Maximum number of images downloaded per page.
        'max_images' => 30,
        // Per-image size cap in kilobytes (default 10 MB).
        'max_image_size_kb' => 10240,
        // Maximum HTML size parsed, in kilobytes.
        'max_html_size_kb' => 5120,
    ],

];
