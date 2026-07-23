<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('Debugbar', \Barryvdh\Debugbar\Facades\Debugbar::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->applyCommentSettingsOverrides();
    }

    /**
     * Let the database-backed admin settings ("نظرات" tab in the Filament
     * settings page, stored via outerweb/settings) override the static
     * defaults in config/comments.php. Wrapped in try/catch so a missing
     * settings table (fresh install, migrations) can never break the site.
     */
    protected function applyCommentSettingsOverrides(): void
    {
        if (! function_exists('setting')) {
            return;
        }

        try {
            if (($value = setting('comments.enabled')) !== null) {
                config(['comments.enabled' => filter_var($value, FILTER_VALIDATE_BOOLEAN)]);
            }

            if (
                ($value = setting('comments.default_status')) !== null
                && in_array($value, ['pending', 'verified'], true)
            ) {
                config(['comments.default_status' => $value]);
            }

            if (($value = setting('comments.min_seconds')) !== null && is_numeric($value)) {
                config(['comments.min_seconds' => (int) $value]);
            }

            if (($value = setting('comments.max_links')) !== null && is_numeric($value)) {
                config(['comments.max_links' => (int) $value]);
            }
        } catch (\Throwable) {
            // Settings table unavailable — keep the config file defaults.
        }
    }
}
