<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Editor score (1-10) for a news item — feeds the payroll report
 * (حقوق و دستمزد). One score per editor per news item.
 */
class EditorRating extends Model
{
    const UPDATED_AT = null;

    protected $guarded = ['id'];

    public function news(): BelongsTo
    {
        return $this->belongsTo(News::class);
    }

    /**
     * The editor who cast the score.
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
