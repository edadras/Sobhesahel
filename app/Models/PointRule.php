<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * قانون امتیاز باشگاه اعضا — نرخ هر رویداد کاملاً از پنل مدیریت قابل‌ویرایش است
 * (تصمیم نهایی ۵-۳ نقشه راه).
 *
 * @property string $code       کد یکتای رویداد (daily_login, comment_approved, ...)
 * @property int    $points     امتیاز (می‌تواند منفی باشد؛ برای هزینه‌ها مانند archive_unlock)
 * @property ?int   $daily_cap  حداکثر دفعات امتیازگیری در روز (null = بدون سقف)
 */
class PointRule extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'points' => 'integer',
        'daily_cap' => 'integer',
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
