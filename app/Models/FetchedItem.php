<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single feed entry fetched from an external news source (WP-13).
 * Lives in the "رصد منابع" cartable until an editor saves or dismisses it.
 */
class FetchedItem extends Model
{
    public const STATUS_NEW = 'new';
    public const STATUS_SAVED = 'saved';
    public const STATUS_DISMISSED = 'dismissed';

    public const STATUSES = [
        self::STATUS_NEW => 'جدید',
        self::STATUS_SAVED => 'ذخیره‌شده',
        self::STATUS_DISMISSED => 'ردشده',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(NewsSource::class, 'news_source_id');
    }

    public function savedNews(): BelongsTo
    {
        return $this->belongsTo(News::class, 'saved_news_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
