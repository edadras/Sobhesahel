<?php

namespace App\Filament\Resources\AdvertiseResource\Widgets;

use App\Models\Advertise;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;

/**
 * گزارش تبلیغات: last-30-days views/clicks chart for one advertise.
 */
class AdvertiseDailyStatsChart extends ChartWidget
{
    public ?Advertise $record = null;

    protected static ?string $heading = 'آمار ۳۰ روز اخیر (نمایش و کلیک)';

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $empty = ['datasets' => [], 'labels' => []];

        if ($this->record === null) {
            return $empty;
        }

        try {
            if (! Advertise::tableReady('advertise_daily_stats')) {
                return $empty;
            }

            $rows = DB::table('advertise_daily_stats')
                ->where('advertise_id', $this->record->id)
                ->where('date', '>=', now()->subDays(29)->toDateString())
                ->orderBy('date')
                ->get()
                ->keyBy(fn ($row) => Carbon::parse($row->date)->toDateString());

            $labels = [];
            $views = [];
            $clicks = [];

            for ($i = 29; $i >= 0; $i--) {
                $day = now()->subDays($i);
                $key = $day->toDateString();

                $labels[] = Jalalian::fromCarbon($day)->format('m/d');
                $views[] = (int) ($rows->get($key)?->views ?? 0);
                $clicks[] = (int) ($rows->get($key)?->clicks ?? 0);
            }

            return [
                'datasets' => [
                    [
                        'label' => 'نمایش',
                        'data' => $views,
                        'borderColor' => 'rgba(59, 130, 246, 1)',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.15)',
                        'fill' => true,
                        'tension' => 0.3,
                    ],
                    [
                        'label' => 'کلیک',
                        'data' => $clicks,
                        'borderColor' => 'rgba(237, 51, 84, 1)',
                        'backgroundColor' => 'rgba(237, 51, 84, 0.15)',
                        'fill' => true,
                        'tension' => 0.3,
                    ],
                ],
                'labels' => $labels,
            ];
        } catch (\Throwable $e) {
            return $empty;
        }
    }

    protected function getType(): string
    {
        return 'line';
    }
}
