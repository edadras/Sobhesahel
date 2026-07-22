<?php

namespace App\Filament\Resources\NoteResource\Widgets;

use App\Models\Note;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class NoteStatWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('یادداشت های امروز',Note::where('status', 'published')->whereDate('created_at', today())->count())
                ->color('primary')
                ->description('یادداشت های منتشر شده روز'),
            Stat::make('منتشر شده', Cache::remember('notes_published_count', now()->addMinutes(10), function () {
                return Note::where('status', 'published')->count();
            }))
                ->description('یادداشت‌هایی که در سایت نمایش داده شده‌اند')
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('پیش‌نویس‌ها', Cache::remember('notes_draft_count', now()->addMinutes(10), function () {
                return Note::where('status', 'draft')->count();
            }))
                ->description('یادداشت‌هایی که هنوز منتشر نشده‌اند')
                ->icon('heroicon-o-document-text')
                ->color('warning'),

            Stat::make('زمان‌بندی شده', Cache::remember('notes_scheduled_count', now()->addMinutes(10), function () {
                return Note::where('status', 'scheduled')->count();
            }))
                ->description('یادداشت‌هایی که برای انتشار در آینده زمان‌بندی شده‌اند')
                ->icon('heroicon-o-calendar')
                ->color('info'),
        ];
    }
}
