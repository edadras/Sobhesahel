<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

/**
 * سفارش رپورتاژ آگهی.
 */
class ReportageOrder extends Model
{
    public const STATUS_NEW = 'new';
    public const STATUS_AWAITING_PAYMENT = 'awaiting_payment';
    public const STATUS_PAID = 'paid';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_REJECTED = 'rejected';

    protected $guarded = ['id'];

    protected $casts = [
        'price' => 'integer',
        'desired_publish_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $order) {
            if (blank($order->token)) {
                $order->token = (string) Str::uuid();
            }
        });
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_NEW => 'جدید',
            self::STATUS_AWAITING_PAYMENT => 'در انتظار پرداخت',
            self::STATUS_PAID => 'پرداخت‌شده',
            self::STATUS_PUBLISHED => 'منتشرشده',
            self::STATUS_REJECTED => 'ردشده',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function converted_news(): BelongsTo
    {
        return $this->belongsTo(News::class, 'converted_news_id');
    }

    public function payUrl(): string
    {
        return route('website.rtl.reportage.pay', ['token' => $this->token]);
    }
}
