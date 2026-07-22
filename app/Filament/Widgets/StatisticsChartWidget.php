<?php

namespace App\Filament\Widgets;

use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Http;

class StatisticsChartWidget extends ChartWidget
{   use HasWidgetShield;

    protected static ?string $heading = 'نمودار بازدیدهای ۷ روز گذشته'; // Persian Title

    protected static bool $isLazy = false; // Loads immediately
    protected static ?string $pollingInterval = '30s'; // Refresh every 30s (30000ms)


    protected static ?int $sort = 2;

    protected function getData(): array
    {
//        $matomoUrl = 'http://145.239.138.55:8011/index.php';
//        $tokenAuth = 'ba67a38bc8b9095dde20cb9870912764'; // Replace with your valid API token
//        $siteId = 1;
//
//        // Fetch daily visit data for the last 7 days
//        $visits = $this->fetchLast7DaysVisits($matomoUrl, $tokenAuth, $siteId);
//
//        return [
//            'datasets' => [
//                [
//                    'label' => 'تعداد بازدیدها', // Persian Label
//                    'data' => array_values($visits),
//                    'borderColor' => 'rgba(75, 192, 192, 1)', // Line color
//                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)', // Fill color
//                ],
//            ],
//            'labels' => array_keys($visits), // Dates as X-axis labels
//        ];
        return  [];
    }

    protected function getType(): string
    {
        return 'line';
    }

    private function fetchLast7DaysVisits($matomoUrl, $tokenAuth, $siteId)
    {
        $response = Http::get($matomoUrl, [
            'module' => 'API',
            'method' => 'VisitsSummary.getVisits',
            'idSite' => $siteId,
            'period' => 'day',
            'date' => 'last7',
            'format' => 'json',
            'token_auth' => $tokenAuth,
        ]);

        // Initialize data with default 0 values for last 7 days
        $dates = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dates[$date] = 0; // Default 0
        }

        if ($response->successful()) {
            $visitsData = $response->json();
            foreach ($visitsData as $date => $count) {
                if (isset($dates[$date])) {
                    $dates[$date] = $count; // Update with real visit count
                }
            }
        }

        return $dates;
    }
}
