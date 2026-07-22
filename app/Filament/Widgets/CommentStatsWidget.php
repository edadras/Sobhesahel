<?php

namespace App\Filament\Widgets;

use App\Models\Comment;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CommentStatsWidget extends BaseWidget
{
    use HasWidgetShield;

    protected static bool $isLazy = false;

    protected ?string $heading = 'آمار دیدگاه‌ها';

    protected static ?int $sort = 5;

    protected function getStats(): array
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        $total = Comment::count();
        $pending = Comment::where('status', 'pending')->count();
        $verified = Comment::where('status', 'verified')->count();
        $rejected = Comment::where('status', 'rejected')->count();

        $todayCount = Comment::whereDate('created_at', $today)->count();
        $yesterdayCount = Comment::whereDate('created_at', $yesterday)->count();
        $diff = $todayCount - $yesterdayCount;

        // Daily totals for the last 7 days (sparkline).
        $weekChart = collect(range(6, 0))->map(
            fn (int $daysAgo) => Comment::whereDate('created_at', Carbon::today()->subDays($daysAgo))->count()
        )->toArray();

        // Top commented news posts.
        $topPosts = Comment::query()
            ->selectRaw('news_id, COUNT(*) as total')
            ->whereNotNull('news_id')
            ->groupBy('news_id')
            ->orderByDesc('total')
            ->with('news:id,title')
            ->limit(3)
            ->get()
            ->map(fn ($row) => mb_substr($row->news?->title ?? 'بدون عنوان', 0, 30) . ' (' . $row->total . ')')
            ->implode(' | ');

        return [
            Stat::make('کل دیدگاه‌ها', number_format($total))
                ->description('مجموع دیدگاه‌های ثبت شده (نمودار ۷ روز اخیر)')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->chart($weekChart)
                ->color('primary'),

            Stat::make('در انتظار بررسی', number_format($pending))
                ->description('نیازمند بررسی مدیر')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('تأیید شده', number_format($verified))
                ->description('دیدگاه‌های منتشر شده')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('رد شده', number_format($rejected))
                ->description('شامل موارد اسپم')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            Stat::make('دیدگاه‌های امروز', number_format($todayCount))
                ->description('دیروز: ' . number_format($yesterdayCount) . ($diff >= 0 ? ' (+' . $diff . ')' : ' (' . $diff . ')'))
                ->descriptionIcon($diff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($diff >= 0 ? 'success' : 'danger'),

            Stat::make('پربحث‌ترین مطالب', $topPosts !== '' ? ' ' : '—')
                ->description($topPosts !== '' ? $topPosts : 'هنوز دیدگاهی ثبت نشده است')
                ->descriptionIcon('heroicon-m-fire')
                ->color('info'),
        ];
    }
}
