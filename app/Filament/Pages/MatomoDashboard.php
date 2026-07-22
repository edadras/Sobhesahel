<?php

namespace App\Filament\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;

class MatomoDashboard extends Page
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar'; // Choose an icon
    protected static ?string $navigationLabel = 'آمار وبسایت'; // Persian title
    protected ?string $heading = 'آمار وبسایت';
    protected static ?string $slug = 'matomo-dashboard';
    protected static string $view = 'filament.pages.matomo-dashboard';
}
