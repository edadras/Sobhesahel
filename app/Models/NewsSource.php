<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * An external news source monitored by the crawler (WP-13).
 */
class NewsSource extends Model
{
    public const TYPES = [
        'rss' => 'RSS 2.0',
        'atom' => 'Atom',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'fetch_interval_minutes' => 'integer',
        'last_fetched_at' => 'datetime',
    ];

    /**
     * Default category applied to news saved from this source.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function fetchedItems(): HasMany
    {
        return $this->hasMany(FetchedItem::class);
    }

    /**
     * Is this source due for a new fetch according to its own interval?
     */
    public function isDue(): bool
    {
        if ($this->last_fetched_at === null) {
            return true;
        }

        $interval = max(1, (int) $this->fetch_interval_minutes);

        return $this->last_fetched_at->addMinutes($interval)->isPast();
    }
}
