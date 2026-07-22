<?php

namespace App\Filament\Widgets;

use App\Services\ContentVisitStats;
use App\Services\MatomoService;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;

class StatisticsOsChartWidget extends ChartWidget
{
    use HasWidgetShield;

    protected static ?string $heading = 'ترکیب بازدیدها';

    protected static bool $isLazy = false;

    protected static ?string $pollingInterval = '120s';

    protected static ?int $sort = 3;

    public function getHeading(): ?string
    {
        return $this->matomoReferrers() !== null
            ? 'منابع ورودی بازدید امروز (ماتومو)'
            : 'توزیع بازدید بر اساس نوع محتوا';
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        try {
            // Prefer the Matomo referrer breakdown; fall back to the internal
            // per-content-type visit distribution when Matomo is unavailable.
            $data = $this->matomoReferrers() ?? ContentVisitStats::visitsByType();

            return [
                'datasets' => [
                    [
                        'data' => array_values($data),
                        'backgroundColor' => [
                            'rgba(237, 51, 84, 0.7)',   // brand red
                            'rgba(54, 162, 235, 0.7)',  // blue
                            'rgba(255, 206, 86, 0.7)',  // yellow
                            'rgba(75, 192, 192, 0.7)',  // teal
                            'rgba(153, 102, 255, 0.7)', // purple
                            'rgba(255, 159, 64, 0.7)',  // orange
                        ],
                    ],
                ],
                'labels' => array_keys($data),
            ];
        } catch (\Throwable $e) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }
    }

    protected function matomoReferrers(): ?array
    {
        try {
            $referrers = MatomoService::referrerTypes();

            return blank($referrers) ? null : $referrers;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
