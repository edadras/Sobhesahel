<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

/**
 * اشتراک ویژه — grants access to digital publications (PDF archive) via
 * a unique access code while active.
 */
class Subscription extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';

    protected $guarded = ['id'];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $subscription) {
            if (blank($subscription->access_code)) {
                $subscription->access_code = self::generateAccessCode();
            }
        });
    }

    /**
     * Short, unambiguous (no 0/O/1/I), unique access code — SMS friendly.
     */
    public static function generateAccessCode(): string
    {
        do {
            $code = strtoupper(Str::random(4)) . random_int(1000, 9999);
            $code = str_replace(['0', 'O', '1', 'I'], ['2', 'P', '3', 'J'], $code);
        } while (static::where('access_code', $code)->exists());

        return $code;
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'در انتظار پرداخت',
            self::STATUS_ACTIVE => 'فعال',
            self::STATUS_EXPIRED => 'منقضی‌شده',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $this->ends_at !== null
            && $this->ends_at->isFuture();
    }

    /**
     * Activate (or re-activate) this subscription for its plan duration.
     */
    public function activate(): self
    {
        $days = (int) ($this->plan?->duration_days ?? 30);

        $this->forceFill([
            'status' => self::STATUS_ACTIVE,
            'starts_at' => $this->starts_at ?? now(),
            'ends_at' => now()->addDays($days),
        ])->save();

        return $this;
    }

    /**
     * SMS the access code to the subscriber — guarded: only runs when
     * enabled in config/payments.php AND a pattern id is configured; any
     * failure is swallowed so it never blocks activation.
     */
    public function sendAccessCodeSms(): bool
    {
        try {
            if (! config('payments.sms.enabled', false)) {
                return false;
            }

            $pattern_id = (string) config('payments.sms.subscription_pattern_id', '');

            if ($pattern_id === '' || blank($this->mobile)) {
                return false;
            }

            return (bool) \App\Helpers\ModirSmsHelper::send_pattern($pattern_id, $this->mobile, [
                'code' => $this->access_code,
            ]);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Find the subscription a given access code unlocks right now, or null.
     */
    public static function findActiveByCode(?string $code): ?self
    {
        $code = strtoupper(trim((string) $code));

        if ($code === '') {
            return null;
        }

        $subscription = static::where('access_code', $code)->first();

        return $subscription !== null && $subscription->isActive() ? $subscription : null;
    }
}
