<?php

namespace App\Filament\Resources\NewsResource\Widgets;

use App\Models\News;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Support\Enums\IconSize;
use Illuminate\Support\Facades\Cache;

class NewsCountStatWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('مجموع اخبار', Cache::remember('news_total_count', now()->addMinutes(10), function () {
                return News::count();
            }))
                ->description('تمامی اخبار موجود در سیستم')
                ->color('primary'),

            Stat::make('منتشر شده', Cache::remember('news_published_count', now()->addMinutes(10), function () {
                return News::where('status', 'published')->count();
            }))
                ->description('اخباری که در سایت نمایش داده شده‌اند')
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('پیش‌نویس‌ها', Cache::remember('news_draft_count', now()->addMinutes(10), function () {
                return News::where('status', 'draft')->count();
            }))
                ->description('اخباری که هنوز منتشر نشده‌اند')
                ->icon('heroicon-o-document-text')
                ->color('warning'),

            Stat::make('زمان‌بندی شده', Cache::remember('news_scheduled_count', now()->addMinutes(10), function () {
                return News::where('status', 'scheduled')->count();
            }))
                ->description('اخباری که برای انتشار در آینده زمان‌بندی شده‌اند')
                ->icon('heroicon-o-calendar')
                ->color('info'),
        ];
    }
}
