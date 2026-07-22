<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    protected $guarded = ['id'];

    public function news()
    {
        return $this->belongsToMany(News::class, 'category_news');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

    /**
     * Get the route for a category by its ID, with caching.
     *
     * @param int $id
     * @return string|null
     */
    public static function getRouteById($id)
    {
        $cacheKey = 'category_route_' . $id;

        return Cache::rememberForever($cacheKey, function () use ($id) {
            $category = self::find($id);

            if (!$category) {
                return null;
            }

            return route('website.rtl.category', ['slug' => $category->slug]);
        });
    }

    public static function getCategoriesForWebsiteMenu()
    {
        return Cache::remember('categories_with_children', 60, function () {
            return Category::with('childrenRecursive')->whereNull('parent_id')->get()->toArray();
        });
    }
}
