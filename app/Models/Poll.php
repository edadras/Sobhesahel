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
        $this->results = collect($this->poll->options)->mapWithKeys(function ($option) {
            return [$option->id => $option->votes()->count()];
        })->toArray();
    }

    public static function getActivePoll()
    {
        return self::where('is_active',true)->first();
    }
}
