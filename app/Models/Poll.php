<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Poll extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'login_required' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($poll) {
            if (empty($poll->user_id)) {
                $poll->user_id = auth()->id();
            }

            if (empty($poll->lang_id)) {
                $poll->lang_id = 1;
            }
        });

        static::updating(function ($poll) {
            if (empty($poll->user_id)) {
                $poll->user_id = auth()->id();
            }
        });
    }


    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }

    public function getResults(): array
    {
        return $this->options()
            ->withCount('votes')
            ->get()
            ->map(fn($option) => [
                'option' => $option->title,
                'votes' => $option->votes_count
            ])->toArray();
    }

    public function fetchResults()
    {
        $this->results = $this->options->mapWithKeys(function ($option) {
            return [$option->id => $option->votes()->count()];
        })->toArray();
    }

    /**
     * آیا نظرسنجی شروع شده است؟ (null = بدون محدودیت)
     */
    public function hasStarted(): bool
    {
        return $this->starts_at === null || $this->starts_at->isPast();
    }

    /**
     * آیا نظرسنجی پایان یافته است؟ (null = بدون محدودیت)
     */
    public function hasEnded(): bool
    {
        return $this->ends_at !== null && $this->ends_at->isPast();
    }

    /**
     * آیا رأی‌گیری در حال حاضر باز است؟
     */
    public function isOpen(): bool
    {
        return $this->is_active && $this->hasStarted() && ! $this->hasEnded();
    }

    /**
     * نظرسنجی فعالی که پنجره انتشار آن شروع شده باشد.
     * نظرسنجی پایان‌یافته همچنان برگردانده می‌شود تا فقط نتایج آن نمایش داده شود.
     */
    public static function getActivePoll()
    {
        return self::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->orderByDesc('id')
            ->first();
    }

    /**
     * دسته‌بندی‌های موجود نظرسنجی‌ها.
     */
    public static function categories(): array
    {
        return self::query()
            ->whereNotNull('poll_category')
            ->where('poll_category', '!=', '')
            ->distinct()
            ->orderBy('poll_category')
            ->pluck('poll_category', 'poll_category')
            ->toArray();
    }
}
