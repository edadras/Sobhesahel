<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
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
        'social_links' => 'array',
        'is_active' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
