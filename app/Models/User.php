<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Edwink\FilamentUserActivity\Traits\UserActivityTrait;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,HasRoles,UserActivityTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = ['id'];

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url ? Storage::url($this->avatar_url) : "/asset/img/user05.png";
    }

    /**
     * Filament panel access (FilamentUser contract). The custom Login page
     * already calls this after credential checks, and Filament's Authenticate
     * middleware enforces it on every panel request.
     *
     * A deactivated user (is_active = false) may not enter the admin panel.
     * NOTE: this only gates panel access — the user's public content (author
     * pages, news, notes, ...) stays untouched.
     *
     * The strict `=== false` comparison keeps users with a missing/NULL
     * is_active value (e.g. code deployed before the migration ran) able to
     * log in, so a deploy can never lock the whole team out.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->is_active === false) {
            Log::warning('Panel access blocked: user account is deactivated.', [
                'user_id' => $this->id,
                'email' => $this->email,
                'panel' => $panel->getId(),
                'ip' => request()?->ip(),
            ]);

            return false;
        }

        return true;
    }

    /**
     * Panel locale options ("زبان پنل").
     *
     * @return array<string, string>
     */
    public static function localeOptions(): array
    {
        return [
            'fa' => 'فارسی',
            'en' => 'انگلیسی (English)',
        ];
    }

    /**
     * Common timezone options with Persian-friendly labels. Keys are valid
     * PHP timezone identifiers.
     *
     * @return array<string, string>
     */
    public static function timezoneOptions(): array
    {
        return [
            'Asia/Tehran' => 'تهران (ایران)',
            'UTC' => 'زمان جهانی (UTC)',
            'Asia/Dubai' => 'دبی (امارات)',
            'Asia/Muscat' => 'مسقط (عمان)',
            'Asia/Qatar' => 'دوحه (قطر)',
            'Asia/Kuwait' => 'کویت',
            'Asia/Riyadh' => 'ریاض (عربستان)',
            'Asia/Baghdad' => 'بغداد (عراق)',
            'Asia/Istanbul' => 'استانبول (ترکیه)',
            'Asia/Baku' => 'باکو (جمهوری آذربایجان)',
            'Asia/Yerevan' => 'ایروان (ارمنستان)',
            'Asia/Kabul' => 'کابل (افغانستان)',
            'Asia/Karachi' => 'کراچی (پاکستان)',
            'Asia/Tashkent' => 'تاشکند (ازبکستان)',
            'Asia/Kolkata' => 'دهلی نو (هند)',
            'Asia/Shanghai' => 'پکن / شانگهای (چین)',
            'Asia/Tokyo' => 'توکیو (ژاپن)',
            'Asia/Kuala_Lumpur' => 'کوالالامپور (مالزی)',
            'Europe/Moscow' => 'مسکو (روسیه)',
            'Europe/Berlin' => 'برلین (آلمان)',
            'Europe/Paris' => 'پاریس (فرانسه)',
            'Europe/London' => 'لندن (انگلستان)',
            'Europe/Stockholm' => 'استکهلم (سوئد)',
            'America/New_York' => 'نیویورک / تورنتو (شرق آمریکا)',
            'America/Chicago' => 'شیکاگو (مرکز آمریکا)',
            'America/Los_Angeles' => 'لس‌آنجلس (غرب آمریکا)',
            'Australia/Sydney' => 'سیدنی (استرالیا)',
        ];
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function news()
    {
        return $this->hasMany(News::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function videos()
    {
        return $this->hasMany(Video::class);
    }

    public function galleries()
    {
        return $this->hasMany(Gallery::class);
    }
}
