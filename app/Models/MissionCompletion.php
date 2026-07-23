<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * پیشرفت/تکمیل یک ماموریت برای یک عضو در یک دوره مشخص.
 *
 * @property string $period_key «2026-07-24» (روزانه)، «2026-W30» (هفتگی) یا «once»
 */
class MissionCompletion extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'member_id' => 'integer',
        'mission_id' => 'integer',
        'progress' => 'integer',
        'completed_at' => 'datetime',
    ];

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }
}
