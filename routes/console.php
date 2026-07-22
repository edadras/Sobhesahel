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

// Refresh market prices (ارز، طلا، سکه) from the configured provider.
// Skipped entirely in "manual" mode, where editors maintain prices by hand.
if (config('prices.provider') !== 'manual') {
    Schedule::command('app:fetch-prices')
        ->everyThirtyMinutes()
        ->withoutOverlapping()
        ->onOneServer();
}
