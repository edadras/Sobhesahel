<?php

return [

    /*
    |--------------------------------------------------------------------------
    | News source crawler (WP-13 — رصد و پایش منابع خبری)
    |--------------------------------------------------------------------------
    |
    | Settings used by App\Services\NewsCrawlerService and the
    | app:crawl-sources command. Each NewsSource row additionally carries its
    | own fetch_interval_minutes, honored by the scheduled run.
    |
    */

    // HTTP timeout (seconds) for feed and image downloads.
    'timeout' => (int) env('CRAWLER_TIMEOUT', 10),

    // User-Agent header sent with every crawler request. Polite bots identify
    // themselves so source sites can whitelist (or rate-limit) the crawler.
    'user_agent' => env(
        'CRAWLER_USER_AGENT',
        'SobheSahelBot/1.0 (+https://sobhesahel.ir; news aggregation crawler)'
    ),

    // Safety cap on the number of feed entries processed per source per run.
    'max_items_per_fetch' => (int) env('CRAWLER_MAX_ITEMS', 50),

    // Fetched items still in status "new" or "dismissed" older than this many
    // days are deleted by the scheduled run. Saved items are always kept
    // (they reference the created news record). 0 disables pruning.
    'prune_after_days' => (int) env('CRAWLER_PRUNE_DAYS', 30),

    // Where downloaded item images are stored. The "public" disk root is the
    // same place the news form's FileUpload stores image_original, so the
    // saved draft behaves exactly like a manually uploaded one.
    'image_disk' => env('CRAWLER_IMAGE_DISK', 'public'),
    'image_directory' => env('CRAWLER_IMAGE_DIRECTORY', ''),

    // Never store downloaded images larger than this (bytes).
    'max_image_bytes' => (int) env('CRAWLER_MAX_IMAGE_BYTES', 10 * 1024 * 1024),

];
