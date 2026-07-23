<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * پلن اشتراک ویژه (اشتراک دیجیتال نشریات).
 */
class SubscriptionPlan extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'price' => 'integer',
        'duration_days' => 'integer',
        'sort' => 'integer',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function formattedPrice(): string
    {
        return Payment::faNumber(number_format((int) $this->price)) . ' ' . config('payments.currency', 'تومان');
    }
}
