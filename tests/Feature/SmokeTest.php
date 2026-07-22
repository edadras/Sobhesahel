<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Minimal smoke tests that only require the application to boot.
 *
 * They intentionally avoid RefreshDatabase / migrations (the fulltext index
 * migration is MySQL-specific) and avoid full HTTP requests (which would boot
 * heavy Filament/Livewire panels or query the database).
 */
class SmokeTest extends TestCase
{
    public function test_application_boots_and_has_a_name(): void
    {
        $this->assertNotEmpty(config('app.name'));
    }

    public function test_application_runs_in_testing_environment(): void
    {
        $this->assertSame('testing', $this->app->environment());
    }

    public function test_critical_classes_exist(): void
    {
        $this->assertTrue(class_exists(\App\Models\News::class));
        $this->assertTrue(class_exists(\App\Services\SiteSearchService::class));
    }

    public function test_home_route_is_registered(): void
    {
        $this->assertTrue(Route::has('website.home'));

        $url = route('website.home');

        $this->assertNotEmpty($url);
    }
}
