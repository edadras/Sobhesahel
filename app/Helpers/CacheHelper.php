<?php
/**
 * GitHub: RoyalHaze
 * Date: 5/15/25
 * Time: 4:55 PM
 **/

namespace App\Helpers;

use App\Http\Resources\ContentMetaDataResource;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class CacheHelper
{
    public static function fetch_popular_news($limit = 5, ?Carbon $from_date = null)
    {
        $from_date = $from_date ?? Carbon::now()->subMonth();

        $cacheKey = 'most_viewed_' . $limit . '_' . $from_date->format('Ymd');

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($limit, $from_date) {
            return ContentMetaDataResource::collection(
                self::whereDate('created_at', '>=', $from_date)
                    ->orderBy('visits', 'desc')
                    ->take($limit)
                    ->get()
            );
        });
    }
}
