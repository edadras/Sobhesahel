<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

/**
 * عضو سایت (باشگاه اعضای صبح ساحل).
 *
 * SEPARATE from App\Models\User (editorial staff) — تصمیم ۵-۵ / تداخل ت۴.
 * Members authenticate through the dedicated `member` guard only; they are
 * not FilamentUser instances and can never access the /admin panel.
 */
class Member extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'birth_date' => 'date',
            'is_active' => 'boolean',
            'mobile_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    /* ----------------------------------------------------------------- *
     *  Mobile helpers
     * ----------------------------------------------------------------- */

    /**
     * Normalize an Iranian mobile number to the canonical 09xxxxxxxxx form:
     * Persian/Arabic digits are converted, separators stripped and the
     * +98 / 0098 / 98 international prefixes folded into the leading 0.
     */
    public static function normalizeMobile(?string $mobile): string
    {
        $mobile = static::normalizeDigits(trim((string) $mobile));
        $mobile = preg_replace('/\D+/', '', $mobile) ?? '';

        if (str_starts_with($mobile, '0098')) {
            $mobile = '0' . substr($mobile, 4);
        } elseif (str_starts_with($mobile, '98') && strlen($mobile) === 12) {
            $mobile = '0' . substr($mobile, 2);
        } elseif (strlen($mobile) === 10 && str_starts_with($mobile, '9')) {
            $mobile = '0' . $mobile;
        }

        return $mobile;
    }

    /**
     * Is the given (already normalized) number a valid Iranian mobile?
     */
    public static function isValidIranianMobile(?string $mobile): bool
    {
        return (bool) preg_match('/^09\d{9}$/', (string) $mobile);
    }

    /**
     * Convert Persian/Arabic digits to ASCII digits.
     */
    public static function normalizeDigits(?string $value): string
    {
        return str_replace(
            ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'],
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            (string) $value
        );
    }

    /* ----------------------------------------------------------------- *
     *  Presentation helpers
     * ----------------------------------------------------------------- */

    public function fullName(): string
    {
        $name = trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));

        return $name !== '' ? $name : 'عضو صبح ساحل';
    }

    public function getFullNameAttribute(): string
    {
        return $this->fullName();
    }

    /**
     * Two-letter initials for the avatar placeholder (e.g. «س‌م»).
     */
    public function initials(): string
    {
        $first = mb_substr(trim((string) $this->first_name), 0, 1);
        $last = mb_substr(trim((string) $this->last_name), 0, 1);
        $initials = trim($first . "\u{200C}" . $last, "\u{200C}");

        return $initials !== '' ? $initials : 'ع';
    }

    /**
     * Public URL of the uploaded avatar, or null when none is set.
     */
    public function avatarUrl(): ?string
    {
        if (blank($this->avatar)) {
            return null;
        }

        try {
            return Storage::disk('public')->url($this->avatar);
        } catch (\Throwable) {
            return null;
        }
    }

    public function hasPassword(): bool
    {
        return ! blank($this->password);
    }

    /* ----------------------------------------------------------------- *
     *  Read-only integrations (تداخل ت۱ — do NOT modify Subscription)
     * ----------------------------------------------------------------- */

    /**
     * The member's currently-active اشتراک ویژه, matched read-only by mobile
     * number against the existing subscriptions table. Never throws — phase-1
     * dashboards must keep working even if the monetization tables are absent.
     */
    public function activeSubscription(): ?Subscription
    {
        try {
            return Subscription::query()
                ->where('mobile', $this->mobile)
                ->where('status', Subscription::STATUS_ACTIVE)
                ->whereNotNull('ends_at')
                ->where('ends_at', '>', now())
                ->orderByDesc('ends_at')
                ->first();
        } catch (\Throwable) {
            return null;
        }
    }
}
