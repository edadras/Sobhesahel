<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * ردیف دفتر کل امتیاز عضو (ledger) — تغییرناپذیر؛ فقط created_at دارد.
 *
 * @property int    $member_id
 * @property int    $points        مثبت = کسب، منفی = خرج
 * @property int    $balance_after موجودی پس از این تراکنش
 * @property string $rule_code
 */
class PointTransaction extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    protected $casts = [
        'member_id' => 'integer',
        'points' => 'integer',
        'balance_after' => 'integer',
        'created_at' => 'datetime',
    ];

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function rule()
    {
        return $this->belongsTo(PointRule::class, 'rule_code', 'code');
    }
}
