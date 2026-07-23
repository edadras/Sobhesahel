<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Regenerate sitemap.xml + sub-sitemaps so Google always sees fresh lastmod data.
Schedule::command('app:sitemap')
    ->twiceDaily(3, 15)
    ->withoutOverlapping()
    ->onOneServer();

// Automatic backups (WP-14): database daily, upload files weekly.
Schedule::command('app:backup --only-db')
    ->dailyAt('03:30')
    ->when(fn () => config('backup.enabled', true))
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('app:backup --only-files')
    ->weeklyOn(5, '04:30') // Fridays
    ->when(fn () => config('backup.enabled', true))
    ->withoutOverlapping()
    ->onOneServer();

// News source crawler (WP-13 — رصد و پایش منابع خبری): fetches due RSS/Atom
// sources (each source honors its own fetch_interval_minutes) and prunes
// stale fetched items per config/crawler.php.
Schedule::command('app:crawl-sources')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->onOneServer();

// Refresh market prices (ارز، طلا، سکه) from the configured provider.
// Skipped entirely in "manual" mode, where editors maintain prices by hand.
if (config('prices.provider') !== 'manual') {
    Schedule::command('app:fetch-prices')
        ->everyThirtyMinutes()
        ->withoutOverlapping()
        ->onOneServer();
}

// درآمدزایی — اشتراک ویژه: mark active subscriptions whose end date has
// passed as expired, once a day (access codes stop unlocking the archive).
Schedule::command('app:expire-subscriptions')
    ->dailyAt('01:15')
    ->withoutOverlapping()
    ->onOneServer();

// Flush cache-buffered advertise impressions/clicks into advertise_daily_stats
// and deactivate ads that reached their max view/click limits.
Schedule::command('app:advertise-flush-stats')
    ->everyTenMinutes()
    ->withoutOverlapping()
    ->onOneServer();
