<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Daily aggregated views/clicks per advertise, flushed from the cache
 * buffer by the app:advertise-flush-stats scheduled command.
 */
class AdvertiseDailyStat extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date',
    ];

    public function advertise(): BelongsTo
    {
        return $this->belongsTo(Advertise::class);
    }
}
