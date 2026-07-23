<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * یک چرخش چرخ شانس توسط یک عضو.
 *
 * @property int $cost_points 0 = چرخش رایگان روزانه
 */
class WheelSpin extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    protected $casts = [
        'member_id' => 'integer',
        'wheel_prize_id' => 'integer',
        'cost_points' => 'integer',
        'created_at' => 'datetime',
    ];

    public function prize(): BelongsTo
    {
        return $this->belongsTo(WheelPrize::class, 'wheel_prize_id');
    }
}
