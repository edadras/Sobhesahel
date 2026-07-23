<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * نشان (دستاورد) باشگاه اعضا.
 *
 * @property string $condition_type  points_total|streak_days|missions_completed|transactions_count|manual
 * @property int    $condition_value آستانه لازم برای دریافت خودکار
 */
class Badge extends Model
{
    public const CONDITION_TYPES = [
        'points_total' => 'مجموع امتیاز کسب‌شده',
        'streak_days' => 'روزهای زنجیره ورود',
        'missions_completed' => 'ماموریت‌های تکمیل‌شده',
        'transactions_count' => 'تعداد تراکنش‌های امتیاز',
        'manual' => 'اهدای دستی',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'condition_value' => 'integer',
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(BadgeMember::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
