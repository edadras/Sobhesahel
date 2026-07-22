<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    /**
     * Author types with Persian labels.
     */
    public const TYPES = [
        'writer' => 'نویسنده',
        'reporter' => 'خبرنگار',
        'photographer' => 'عکاس',
        'translator' => 'مترجم',
        'analyst' => 'تحلیلگر',
        'economist' => 'اقتصاددان',
    ];

    /**
     * Supported social networks with Persian labels.
     */
    public const SOCIAL_NETWORKS = [
        'instagram' => 'اینستاگرام',
        'telegram' => 'تلگرام',
        'x' => 'ایکس (توییتر)',
        'linkedin' => 'لینکدین',
        'whatsapp' => 'واتساپ',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'work_history' => 'array',
        'social_links' => 'array',
    ];

    public function getTypeLabelAttribute(): ?string
    {
        return self::TYPES[$this->type] ?? null;
    }

    public function news()
    {
        return $this->hasMany(News::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function videos()
    {
        return $this->hasMany(Video::class);
    }

    public function galleries()
    {
        return $this->hasMany(Gallery::class);
    }
}
