<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ماموریت باشگاه اعضا (روزانه/هفتگی/یک‌باره).
 *
 * @property string $period     daily|weekly|once
 * @property string $event_code رویدادی که پیشرفت را جلو می‌برد (هم‌نام کدهای قوانین امتیاز)
 * @property int    $goal_count تعداد دفعات لازم برای تکمیل
 */
class Mission extends Model
{
    public const PERIODS = [
        'daily' => 'روزانه',
        'weekly' => 'هفتگی',
        'once' => 'یک‌باره',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'points' => 'integer',
        'goal_count' => 'integer',
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];

    public function completions(): HasMany
    {
        return $this->hasMany(MissionCompletion::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
