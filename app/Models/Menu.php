<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class Menu extends Model
{
    protected $guarded = ['id'];

    public const TYPES = [
        'link' => 'لینک',
        'content' => 'محتوا',
        'separator' => 'جداکننده',
    ];

    public const LOCATIONS = [
        'header' => 'هدر',
        'footer' => 'فوتر',
        'mobile' => 'موبایل',
    ];

    public const TARGETS = [
        '_self' => 'همین صفحه',
        '_blank' => 'صفحه جدید',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        $flush = function () {
            foreach (array_keys(self::LOCATIONS) as $location) {
                Cache::forget('website_menu_' . $location);
            }
        };

        static::saved($flush);
        static::deleted($flush);
    }

    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('order');
    }

    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

    public function activeChildrenRecursive()
    {
        return $this->children()->where('is_active', true)->with('activeChildrenRecursive');
    }

    /**
     * Resolve a stored menu URL to an absolute one.
     * Absolute URLs are kept as-is, relative paths are prefixed with the app URL.
     */
    public static function resolveUrl(?string $url): string
    {
        if ($url === null || $url === '') {
            return '#';
        }

        if (preg_match('/^(https?:)?\/\//i', $url) || str_starts_with($url, '#')) {
            return $url;
        }

        return url($url);
    }

    /**
     * Cached, active menu tree for a website location (header|footer|mobile).
     * Returns an array of root items, each with an 'active_children_recursive' array.
     */
    public static function forLocation(string $location): array
    {
        return Cache::remember('website_menu_' . $location, now()->addHour(), function () use ($location) {
            if (! Schema::hasTable('menus')) {
                return [];
            }

            return self::with('activeChildrenRecursive')
                ->whereNull('parent_id')
                ->where('location', $location)
                ->where('is_active', true)
                ->orderBy('order')
                ->get()
                ->toArray();
        });
    }
}
