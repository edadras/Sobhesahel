<?php

namespace App\Filament\Widgets;

use App\Services\ContentVisitStats;
use App\Services\MatomoService;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Edwink\FilamentUserActivity\Models\UserActivity;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatisticsWidget extends BaseWidget
{
    use HasWidgetShield;

    protected ?string $heading = 'آمار بازدید';

    protected static bool $isLazy = false;

    protected static ?string $pollingInterval = '60s';

    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        try {
            return array_merge(
                [$this->onlineEditorsStat()],
                $this->contentVisitStats(),
                $this->matomoStats(),
            );
        } catch (\Throwable $e) {
            // The dashboard must never break because of statistics.
            return [];
        }
    }

    /**
     * Panel users active in the last 5 minutes (filament-user-activity data).
     */
    protected function onlineEditorsStat(): Stat
    {
        $online = 0;

        try {
            $online = UserActivity::query()
                ->where('created_at', '>=', now()->subMinutes(5))
                ->distinct('user_id')
                ->count('user_id');
        } catch (\Throwable $e) {
            // Table not migrated yet — show zero.
        }

        return Stat::make('اعضای آنلاین تحریریه', number_format($online))
            ->description('کاربران فعال پنل در ۵ دقیقه گذشته')
            ->color($online > 0 ? 'success' : 'gray')
            ->icon('heroicon-o-user-group');
    }

    /**
     * Internal content visit totals from the `visits` counters.
     *
     * @return array<Stat>
     */
    protected function contentVisitStats(): array
    {
        $todayVisits = ContentVisitStats::visitsSince(Carbon::today());
        $weekVisits = ContentVisitStats::visitsSince(Carbon::today()->subDays(6));
        $monthVisits = ContentVisitStats::visitsSince(Carbon::today()->subDays(29));

        $sparkline = array_values(ContentVisitStats::dailyVisits(7));

        return [
            Stat::make('بازدید مطالب امروز', number_format($todayVisits))
                ->description('مجموع بازدید مطالب منتشرشده امروز')
                ->chart($sparkline)
                ->color('primary')
                ->icon('heroicon-o-eye'),

            Stat::make('بازدید مطالب ۷ روز گذشته', number_format($weekVisits))
                ->description('مجموع بازدید مطالب منتشرشده در هفته اخیر')
                ->color('info')
                ->icon('heroicon-o-chart-bar'),

            Stat::make('بازدید مطالب ۳۰ روز گذشته', number_format($monthVisits))
                ->description('مجموع بازدید مطالب منتشرشده در ماه اخیر')
                ->color('warning')
                ->icon('heroicon-o-calendar-days'),
        ];
    }

    /**
     * Live site-wide numbers from Matomo. Shows "—" placeholders whenever
     * Matomo is unconfigured or unreachable.
     *
     * @return array<Stat>
     */
    protected function matomoStats(): array
    {
        $realtime = null;
        $summary = null;

        try {
            $realtime = MatomoService::realtimeVisitors();
            $summary = MatomoService::todaySummary();
        } catch (\Throwable $e) {
            // Never break the dashboard because of Matomo.
        }

        $unavailable = 'ماتومو در دسترس نیست';

        return [
            Stat::make('بازدیدکنندگان آنلاین سایت', $realtime !== null ? number_format($realtime) : '—')
                ->description($realtime !== null ? 'بازدیدکنندگان فعال سایت در ۵ دقیقه گذشته' : $unavailable)
                ->color($realtime !== null ? 'danger' : 'gray')
                ->icon('heroicon-o-signal'),

            Stat::make('بازدید امروز سایت', $summary !== null ? number_format($summary['visits']) : '—')
                ->description(
                    $summary !== null
                        ? (
                            $summary['unique'] !== null
                                ? number_format($summary['unique']).' بازدیدکننده یکتا'
                                : 'مجموع بازدیدهای ثبت‌شده امروز'
                        )
                        : $unavailable
                )
                ->color($summary !== null ? 'success' : 'gray')
                ->icon('heroicon-o-globe-alt'),
        ];
    }
}
