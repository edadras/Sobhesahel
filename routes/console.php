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
