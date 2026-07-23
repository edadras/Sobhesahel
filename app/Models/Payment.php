<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

/**
 * پرداخت — a single payment attempt for any payable (رپورتاژ، اشتراک، ...).
 */
class Payment extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';
    public const STATUS_MANUAL_REVIEW = 'manual_review';

    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'integer',
        'paid_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $payment) {
            if (blank($payment->token)) {
                $payment->token = (string) Str::uuid();
            }
        });
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'در انتظار پرداخت',
            self::STATUS_MANUAL_REVIEW => 'در انتظار بررسی',
            self::STATUS_PAID => 'پرداخت‌شده',
            self::STATUS_FAILED => 'ناموفق',
        ];
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Mark this payment paid (idempotent).
     */
    public function markPaid(?string $ref_code = null): self
    {
        $this->forceFill([
            'status' => self::STATUS_PAID,
            'ref_code' => $ref_code ?? $this->ref_code,
            'paid_at' => $this->paid_at ?? now(),
        ])->save();

        return $this;
    }

    /**
     * Amount formatted with the configured currency, e.g. «۱,۵۰۰,۰۰۰ تومان».
     */
    public function formattedAmount(): string
    {
        return self::faNumber(number_format((int) $this->amount)) . ' ' . config('payments.currency', 'تومان');
    }

    /**
     * Convert Latin digits in a string/number to Persian digits.
     */
    public static function faNumber(string|int $value): string
    {
        return strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
    }
}
