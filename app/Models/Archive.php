<?php

namespace App\Models;

use App\Traits\ContentTrait;
use App\Traits\HasTitleValues;
use App\Traits\ImageOptimizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Archive extends Model
{
    use ContentTrait,HasTitleValues,ImageOptimizer;

    /**
     * Publication kinds (roadmap: روزنامه / ویژه‌نامه / فصلنامه / پرونده).
     */
    public const TYPES = [
        'daily' => 'روزنامه',
        'special' => 'ویژه‌نامه',
        'quarterly' => 'فصلنامه',
        'dossier' => 'پرونده',
    ];

    protected $guarded = ['id'];

    public function archive_category()
    {
        return $this->belongsTo(ArchiveCategory::class,'category_id');
    }

    /**
     * News items attached to this publication issue, in manual order.
     */
    public function news(): BelongsToMany
    {
        return $this->belongsToMany(News::class, 'archive_news')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderBy('archive_news.sort_order');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? self::TYPES['daily'];
    }
}
