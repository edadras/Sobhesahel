<?php

namespace App\Filament\Resources\AdvertiseResource\Widgets;

use App\Models\Advertise;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

/**
 * گزارش تبلیغات: per-ad totals + CTR shown on the ViewAdvertise page.
 */
class AdvertiseStatsOverview extends BaseWidget
{
    public ?Advertise $record = null;

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        if ($this->record === null) {
            return [];
        }

        $views = (int) $this->record->view;
        $clicks = (int) $this->record->click;
        $ctr = $views > 0 ? round(($clicks / $views) * 100, 2) : 0;

        $last30Views = 0;
        $last30Clicks = 0;

        try {
            if (Advertise::tableReady('advertise_daily_stats')) {
                $row = DB::table('advertise_daily_stats')
                    ->where('advertise_id', $this->record->id)
                    ->where('date', '>=', now()->subDays(30)->toDateString())
                    ->selectRaw('COALESCE(SUM(views),0) as views, COALESCE(SUM(clicks),0) as clicks')
                    ->first();

                $last30Views = (int) ($row->views ?? 0);
                $last30Clicks = (int) ($row->clicks ?? 0);
            }
        } catch (\Throwable $e) {
            // stats table not ready — show totals only
        }

        return [
            Stat::make('نمایش کل', number_format($views))
                ->description('نمایش ۳۰ روز اخیر: ' . number_format($last30Views))
                ->icon('heroicon-o-eye'),

            Stat::make('کلیک کل', number_format($clicks))
                ->description('کلیک ۳۰ روز اخیر: ' . number_format($last30Clicks))
                ->icon('heroicon-o-cursor-arrow-rays'),

            Stat::make('نرخ کلیک (CTR)', $ctr . '٪')
                ->description('نسبت کلیک به نمایش')
                ->icon('heroicon-o-chart-bar'),
        ];
    }
}
