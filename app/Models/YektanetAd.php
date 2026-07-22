<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class YektanetAd extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'page_scopes' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => self::clearCache());
        static::deleted(fn () => self::clearCache());
    }

    public static function getForPosition(string $position): Collection
    {
        $pageType = self::resolveCurrentPageType();
        $cacheKey = "yektanet_ads.{$position}.{$pageType}";

        return Cache::remember($cacheKey, 3600, function () use ($position, $pageType) {
            return self::query()
                ->where('is_active', true)
                ->where('position', $position)
                ->where(function ($query) use ($pageType) {
                    $query->whereJsonContains('page_scopes', 'all')
                        ->orWhereJsonContains('page_scopes', $pageType);
                })
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
        });
    }

    public static function resolveCurrentPageType(): string
    {
        $route = request()->route();

        if ($route === null) {
            return 'unknown';
        }

        return match ($route->getName()) {
            'website.home' => 'home',
            'website.rtl.single' => match ($route->parameter('type')) {
                'news' => 'news_single',
                'note' => 'note_single',
                'podcast' => 'podcast_single',
                'video' => 'video_single',
                'photo' => 'photo_single',
                default => 'unknown',
            },
            'website.rtl.index' => match ($route->parameter('type')) {
                'news' => 'news_list',
                'note' => 'note_list',
                'podcast' => 'podcast_list',
                'video' => 'video_list',
                'photo' => 'photo_list',
                default => 'unknown',
            },
            'website.rtl.category' => 'category',
            'website.rtl.tag' => 'tag',
            'website.rtl.search' => 'search',
            'website.rtl.contact' => 'contact',
            'website.rtl.about' => 'about',
            'website.rtl.archive', 'website.rtl.archive.pdf' => 'archive',
            'website.rtl.author' => 'author',
            default => 'unknown',
        };
    }

    public static function clearCache(): void
    {
        foreach (array_keys(\App\Support\YektanetAdConfig::POSITIONS) as $position) {
            foreach (array_keys(\App\Support\YektanetAdConfig::PAGE_SCOPES) as $pageScope) {
                Cache::forget("yektanet_ads.{$position}.{$pageScope}");
            }
            Cache::forget("yektanet_ads.{$position}.unknown");
        }
    }
}
