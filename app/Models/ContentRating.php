<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Public 1-5 star rating cast on a content item (news, note, ...).
 * One rating per ip per item (enforced by a unique index).
 */
class ContentRating extends Model
{
    const UPDATED_AT = null;

    protected $guarded = ['id'];

    public function rateable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
