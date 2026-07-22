<?php

namespace App\Filament\Widgets;

use App\Models\Gallery;
use App\Models\News;
use App\Models\Note;
use App\Models\Podcast;
use App\Models\Video;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TotalStatWidget extends BaseWidget
{
    protected static bool $isLazy = false;

    protected ?string $heading = 'اطلاعات سیستم';

    use HasWidgetShield;

    protected function getStats(): array
    {
        $today = Carbon::today();

        $generateChartData = function ($model) {
            return collect(range(6, 0))->map(function ($daysAgo) use ($model) {
                return $model::whereDate('publish_at', Carbon::today()->subDays($daysAgo))->count();
            })->toArray();
        };

        return [
            Stat::make('ورژن نرم افزار', '1.4.1')
                ->description('')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),

            Stat::make('اخبار امروز', $newsCount = News::where('publish_at', '>=', $today)->count())
                ->description('تعداد اخبار منتشر شده امروز')
                ->descriptionIcon('heroicon-m-document-text')
                ->chart($generateChartData(News::class)) // اصلاح شده
                ->color('success'),

            Stat::make('ویدیوهای امروز', $videoCount = Video::where('publish_at', '>=', $today)->count())
                ->description('تعداد ویدیوهای منتشر شده امروز')
                ->descriptionIcon('heroicon-m-video-camera')
                ->chart($generateChartData(Video::class)) // اصلاح شده
                ->color('info'),

            Stat::make('گالری‌های امروز', $galleryCount = Gallery::where('publish_at', '>=', $today)->count())
                ->description('تعداد تصاویر اضافه‌شده امروز')
                ->descriptionIcon('heroicon-m-photo')
                ->chart($generateChartData(Gallery::class)) // اصلاح شده
                ->color('warning'),

            Stat::make('یادداشت‌های امروز', $noteCount = Note::where('publish_at', '>=', $today)->count())
                ->description('تعداد یادداشت‌های منتشر شده امروز')
                ->descriptionIcon('heroicon-m-pencil')
                ->chart($generateChartData(Note::class)) // اصلاح شده
                ->color('primary'),

            Stat::make('پادکست‌های امروز', $podcastCount = Podcast::where('publish_at', '>=', $today)->count())
                ->description('تعداد پادکست‌های منتشر شده امروز')
                ->descriptionIcon('heroicon-m-microphone')
                ->chart($generateChartData(Podcast::class)) // اصلاح شده
                ->color('danger'),
        ];
    }
}
