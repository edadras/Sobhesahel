<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Raw click/impression event log for advertises (گزارش آماری تبلیغات).
 */
class AdvertiseEvent extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function advertise(): BelongsTo
    {
        return $this->belongsTo(Advertise::class);
    }
}
