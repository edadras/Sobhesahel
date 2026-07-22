<?php

namespace App\Filament\Resources\UserResource\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Morilog\Jalali\Jalalian;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class UserPostsSummaryWidget extends BaseWidget
{
    public $record = null; // Filament passes the current record when in the "View/Edit" page context

    protected static bool $isLazy = false;

    protected static ?string $pollingInterval = '0';

    protected function getStats(): array
    {
        $userId = $this->record?->id;



        // Helper: count for each model
        if ($this->record instanceof User){
            $countFromModels = function (Collection $models, $callback) use ($userId) {
                return $models->sum(function ($model) use ($callback, $userId) {
                    return $model::where('user_id', $userId)->where($callback)->count();
                });
            };

            // All models to check
            $models = [
                \App\Models\News::class,
                \App\Models\Note::class,
                \App\Models\Gallery::class,
                \App\Models\Video::class,
                \App\Models\Archive::class,
                \App\Models\Podcast::class,
            ];
        }else{
            $countFromModels = function (Collection $models, $callback) use ($userId) {
                return $models->sum(function ($model) use ($callback, $userId) {
                    return $model::where('author_id', $userId)->where($callback)->count();
                });
            };

            // All models to check
            $models = [
                \App\Models\News::class,
                \App\Models\Note::class,
                \App\Models\Gallery::class,
                \App\Models\Video::class,
                \App\Models\Podcast::class,
            ];
        }

        $modelsCollection = collect($models);

        // Today
        $todayStart = Carbon::today();
        $todayEnd = Carbon::today()->endOfDay();

        // Jalali month start/end (this month)
        $nowJalali = Jalalian::fromCarbon(Carbon::now());
        $thisMonthStart = $nowJalali->getFirstDayOfMonth()->toCarbon();
        $thisMonthEnd = $nowJalali->getEndDayOfMonth()->toCarbon();

        // Jalali month start/end (last month)
        $lastMonthJalali = $nowJalali->subMonths(1);
        $lastMonthStart = $lastMonthJalali->getFirstDayOfMonth()->toCarbon();
        $lastMonthEnd = $lastMonthJalali->getEndDayOfMonth()->toCarbon();

        // Counts
        $totalPosts = $countFromModels($modelsCollection, fn ($q) => $q); // no date filter
        $todayPosts = $countFromModels($modelsCollection, fn ($q) => $q->whereBetween('created_at', [$todayStart, $todayEnd]));
        $thisMonthPosts = $countFromModels($modelsCollection, fn ($q) => $q->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd]));
        $lastMonthPosts = $countFromModels($modelsCollection, fn ($q) => $q->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd]));

        return [
            Stat::make('تمام پست ها', number_format($totalPosts))
                ->icon('heroicon-o-clock')
                ->color('primary'),

            Stat::make('پست های امروز', number_format($todayPosts))
                ->icon('heroicon-o-calendar')
                ->color($todayPosts > 0 ? 'success' : 'secondary'),

            Stat::make('پست های ماه', number_format($thisMonthPosts))
                ->icon('heroicon-o-calendar-days')
                ->color($thisMonthPosts > 0 ? 'success' : 'secondary'),

            Stat::make('پست های ماه پیش', number_format($lastMonthPosts))
                ->icon('heroicon-o-archive-box')
                ->color($lastMonthPosts > 0 ? 'warning' : 'secondary'),
        ];
    }
}
