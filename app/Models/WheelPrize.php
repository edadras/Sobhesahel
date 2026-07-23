<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * جایزه چرخ شانس — لیست جوایز، وزن احتمال و موجودی کاملاً از پنل مدیریت
 * قابل‌تعریف است (تصمیم نهایی ۵-۳ نقشه راه).
 *
 * @property string  $type  points|nothing|coupon_text
 * @property ?string $value مقدار امتیاز (برای points) یا متن کد تخفیف (برای coupon_text)
 * @property ?int    $stock null = نامحدود
 */
class WheelPrize extends Model
{
    public const TYPES = [
        'points' => 'امتیاز',
        'nothing' => 'پوچ',
        'coupon_text' => 'کد تخفیف (متنی)',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'weight' => 'integer',
        'stock' => 'integer',
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * امتیاز این جایزه (فقط برای نوع points معنا دارد).
     */
    public function pointsValue(): int
    {
        return $this->type === 'points' ? (int) $this->value : 0;
    }
}
