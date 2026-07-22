<?php

namespace App\Filament\Widgets;

use App\Services\ContentVisitStats;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Morilog\Jalali\Jalalian;

class StatisticsChartWidget extends ChartWidget
{
    use HasWidgetShield;

    protected static ?string $heading = 'روند بازدید مطالب (۱۴ روز گذشته)';

    protected static bool $isLazy = false;

    protected static ?string $pollingInterval = '60s';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        try {
            $daily = ContentVisitStats::dailyVisits(14);

            $labels = [];
            foreach (array_keys($daily) as $date) {
                $labels[] = Jalalian::fromCarbon(Carbon::parse($date))->format('m/d');
            }

            return [
                'datasets' => [
                    [
                        'label' => 'تعداد بازدیدها',
                        'data' => array_values($daily),
                        'borderColor' => 'rgba(237, 51, 84, 1)',
                        'backgroundColor' => 'rgba(237, 51, 84, 0.15)',
                        'fill' => true,
                        'tension' => 0.3,
                    ],
                ],
                'labels' => $labels,
            ];
        } catch (\Throwable $e) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }
    }

    protected function getType(): string
    {
        return 'line';
    }
}
