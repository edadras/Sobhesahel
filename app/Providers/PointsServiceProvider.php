<?php

namespace App\Providers;

use App\Listeners\AwardMemberPoints;
use App\Services\Points\ClubService;
use App\Services\Points\PointsService;
use Illuminate\Support\ServiceProvider;

/**
 * ثبت موتور امتیاز و باشگاه اعضا (فاز ۲ نقشه راه — U3/U4/U5).
 *
 * سرویس‌ها singleton هستند و اتصال رویدادمحور امتیاز (نظر تأییدشده،
 * رأی نظرسنجی، امتیازدهی محتوا) اینجا bind می‌شود — نه در فایل‌های
 * آن ماژول‌ها.
 */
class PointsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PointsService::class);
        $this->app->singleton(ClubService::class);
    }

    public function boot(): void
    {
        try {
            AwardMemberPoints::register();
        } catch (\Throwable) {
            // موتور امتیاز هرگز نباید بالا آمدن برنامه را مختل کند.
        }
    }
}
