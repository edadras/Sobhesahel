<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * اتصال عضو ↔ نشان (چه زمانی کدام نشان به کدام عضو داده شده).
 */
class BadgeMember extends Model
{
    protected $table = 'badge_member';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'member_id' => 'integer',
        'badge_id' => 'integer',
        'awarded_at' => 'datetime',
    ];

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }
}
