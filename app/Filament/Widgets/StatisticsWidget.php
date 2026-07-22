<?php

namespace App\Filament\Widgets;

use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Http;

class StatisticsWidget extends BaseWidget
{
    use HasWidgetShield;

    protected ?string $heading = 'آمار بازدید';

    protected static bool $isLazy = false;

    protected static ?string $pollingInterval = '5s';

    protected static ?int $sort = 0;

    protected function getStats(): array
    {
//        $matomoUrl = 'http://145.239.138.55:8011/index.php';
//        $tokenAuth = 'ba67a38bc8b9095dde20cb9870912764';
//        $siteId = 1;
//
//        try {
//            // Fetch Unique Visitors Today
//            $uniqueVisitors = $this->fetchMatomoData($matomoUrl, $tokenAuth, $siteId, 'VisitsSummary.getUniqueVisitors', 'day')['value'];
//
//            // Fetch Total Visits Today
//            $totalVisits = $this->fetchMatomoData($matomoUrl, $tokenAuth, $siteId, 'VisitsSummary.getVisits', 'day')['value'];
//
////        // Fetch Real-Time Visitors (Last 30 min)
//            $realTimeVisitors = $this->fetchRealTimeVisitors($matomoUrl, $tokenAuth, $siteId);
//
//            return [
//                Stat::make('بازدیدکنندگان آنلاین', number_format($realTimeVisitors))
//                    ->description('تعداد بازدیدکنندگان فعال در ۵ دقیقه گذشته')
//                    ->color('danger') // Red color
//                    ->icon('heroicon-o-eye'),
//
//                Stat::make('بازدیدکنندگان یکتا امروز', number_format($uniqueVisitors))
//                    ->description('تعداد بازدیدکنندگان یکتا امروز')
//                    ->color('success') // Green color
//                    ->icon('heroicon-o-user-group'),
//
//                Stat::make('تعداد کل بازدیدهای امروز', number_format($totalVisits))
//                    ->description('مجموع بازدیدهای ثبت شده امروز')
//                    ->color('primary') // Blue color
//                    ->icon('heroicon-o-chart-bar'),
//            ];
        return [];
//        }catch (\Exception $e){
//            return [];
//        }
    }

    private function fetchRealTimeVisitors($matomoUrl, $tokenAuth, $siteId)
    {
        $response = Http::get($matomoUrl, [
            'module' => 'API',
            'method' => 'Live.getCounters',
            'idSite' => $siteId,
            'lastMinutes' => 5, // Change this value to 5, 10, or 30 for different time ranges
            'format' => 'json',
            'token_auth' => $tokenAuth,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data[0]['visitors'] ?? 0;
        }

        return 0;
    }

    private function fetchMatomoData($matomoUrl, $tokenAuth, $siteId, $method, $periodOrTime)
    {
        $response = Http::get($matomoUrl, [
            'module' => 'API',
            'method' => $method,
            'idSite' => $siteId,
            'period' => is_numeric($periodOrTime) ? null : $periodOrTime,
            'date' => is_numeric($periodOrTime) ? null : 'today',
            'lastMinutes' => is_numeric($periodOrTime) ? $periodOrTime : null,
            'format' => 'json',
            'token_auth' => $tokenAuth,
        ]);

        if ($response->successful()) {
            return is_array($response->json()) ? ($response->json()[0]['visitors'] ?? $response->json()) : 0;
        }

        return 0;
    }
}
