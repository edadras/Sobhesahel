<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class HomeBox extends Model
{
    protected $guarded = ['id'];

    /**
     * Layout kinds actually supported by the home page blade
     * (website/rtl/index-oh.blade.php).
     */
    public const TYPES = [
        'category_row' => 'ردیف دسته‌بندی (تا ۳ ستون)',
        'wide_category' => 'باکس عریض تک‌دسته (مانند خلیج فارس)',
        'video' => 'باکس ویدئو',
        'photos' => 'باکس عکس',
        'top_row' => 'باکس سرمقاله، یادداشت و پادکست',
    ];

    public const CONTENT_TYPES = [
        'news' => 'خبر',
        'note' => 'یادداشت',
        'video' => 'ویدئو',
        'podcast' => 'پادکست',
        'photo' => 'عکس',
        'mixed' => 'ترکیبی',
    ];

    protected $casts = [
        'category_ids' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        $flush = function () {
            Cache::forget('website_index_page');
        };

        static::saved($flush);
        static::deleted($flush);
    }
}
