<?php

/**
 * GitHub: RoyalHaze
 * Date: 2/27/25
 * Time: 6:07 PM
 **/

namespace App\Traits;

use App\Constant\AppConstant;
use App\Http\Resources\ContentMetaDataResource;
use App\Http\Resources\Website\AuthorProfileResource;
use App\Models\Archive;
use App\Models\Author;
use App\Models\Category;
use App\Models\Comment;
use App\Models\News;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
//use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\InteractsWithMedia;

trait ContentTrait
{
//    use InteractsWithMedia;
//
//    public function registerMediaConversions(\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
//    {
//        $this->addMediaConversion('webp')
//            ->format('webp')
//            ->width(800)
//            ->optimize()
//            ->queued();
//
//        $this->addMediaConversion('thumb')
//            ->width(300)
//            ->height(200)
//            ->sharpen(10)
//            ->optimize()
//            ->queued();
//    }

    protected static function bootContentTrait()
    {
        static::saved(fn($model) => static::clearCache($model));
        static::deleted(fn($model) => static::clearCache($model));

        static::creating(function ($model) {
            if (empty($model->user_id)) {
                $model->user_id = auth()->id();
            }

            if (empty($model->lang_id)) {
                $model->lang_id = 1;
            }

            if (empty($model->publish_at)) {
                $model->publish_at = now();
            }

            if (! ($model instanceof Archive)) {
                if (empty($model->slug)) {
                    $model->slug = self::generateSlug($model->title);
                } else {
                    $model->slug = self::sanitizeSlug($model->slug);
                }
            }

            static::updatePublicationStatus($model);
        });

        static::updating(function ($model) {
            if (empty($model->user_id)) {
                $model->user_id = auth()->id();
            }


            if (! ($model instanceof Archive)) {
                if (empty($model->slug)) {
                    $model->slug = self::generateSlug($model->title);
                } else {
                    $model->slug = self::sanitizeSlug($model->slug);
                }
            }

            static::updatePublicationStatus($model);


        });
    }

    public static function generateSlug($title)
    {
        return preg_replace('/[^a-zA-Z0-9آ-ی۰-۹\-]/u', '-', $title);
    }

    protected static function sanitizeSlug($slug)
    {
        return preg_replace('/[^a-zA-Z0-9آ-ی۰-۹\-]/u', '-', $slug);
    }

    protected static function updatePublicationStatus($model)
    {
        if ($model->status === 'published') {
            $model->is_published = true;
        } else {
            $model->is_published = false;
        }

        if ($model->status === 'scheduled' && request()->has('publish_at')) {
            $model->publish_at = request()->input('publish_at');
        }
    }

    protected static function clearCache($model = null)
    {
        Cache::forget('website_index_page');
        Cache::forget('latest_news');

        if ($model) {
            Cache::forget("related_news_{$model->id}");
            Cache::forget("related_news_tags_{$model->id}");
            Cache::forget("related_news_categories_{$model->id}");
        }


        $commonLimits = [5, 15, 20];
        foreach ($commonLimits as $limit) {
            Cache::forget('latest_posts_' . $limit);
        }


        if ($model instanceof News){
            // Clear category cache when a news post is saved (created or updated)
            static::saving(function ($post) {
                $categorySlugs = $post->categories->pluck('slug')->toArray();

                foreach ($categorySlugs as $slug) {
                    $cacheKey = 'category_posts_' . $slug . '_lang_1';
                    Cache::forget($cacheKey);
                }
            });

            // Clear category cache when a news post is deleted
            static::deleting(function ($post) {
                $categorySlugs = $post->categories->pluck('slug')->toArray();

                foreach ($categorySlugs as $slug) {
                    $cacheKey = 'category_posts_' . $slug . '_lang_1';
                    Cache::forget($cacheKey);
                }
            });
        }
    }

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getUrl($data = null)
    {
        $type = strtolower(class_basename($this));

        if ($type === 'gallery') {
            $type = 'photo';
        }

        $route_prefix = ($this->lang_id == 1) ? 'website.rtl.' : 'website.ltr.';

        $default_types = ['news', 'note', 'podcast', 'video', 'photo'];

        if (in_array($type, $default_types)) {
            return route($route_prefix . 'single', [
                'slug' => $this->slug,
                'code' => $this->id,
                'type' => $type,
            ]);
        }

        if ($type == 'archive') {
            return route('website.rtl.archive.pdf', [
                'archive' => $this->archive_category->slug,
                'number' => $this->archive_number,
            ]);
        }

        return route($route_prefix . AppConstant::post_type_routes($type), [
            'slug' => $this->slug,
            'code' => $this->id,
        ]);
    }

    public function getShortUrl($data = null)
    {
        $type = strtolower(class_basename($this));

        if ($type === 'gallery') {
            $type = 'photo';
        }

        $route_prefix = ($this->lang_id == 1) ? 'website.rtl.' : 'website.ltr.';

        $default_types = ['news', 'note', 'podcast', 'video', 'photo'];

        if (in_array($type, $default_types)) {
            return route($route_prefix . 'short_single', [
//                'slug' => $this->slug,
                'code' => $this->id,
                'type' => $type,
            ]);
        }

        if ($type == 'archive') {
            return route('website.rtl.archive.pdf', [
                'archive' => $this->archive_category->slug,
                'number' => $this->archive_number,
            ]);
        }

        return route($route_prefix . AppConstant::post_type_routes($type), [
//            'slug' => $this->slug,
            'code' => $this->id,
        ]);
    }

    /**
     * Get the most viewed posts for the model using ContentMetaDataResource
     *
     * @param int $limit
     * @param \Carbon\Carbon|null $from_date
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public static function getMostViewed($limit = 5, ?Carbon $from_date = null)
    {
        $from_date = $from_date ?? Carbon::now()->subMonth();

        $cacheKey = 'most_viewed_' . $limit . '_' . $from_date->format('y-m-d');

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($limit, $from_date) {
            return ContentMetaDataResource::collection(
                self::where('created_at', '>=', $from_date)
                    ->orderBy('visits', 'desc')
                    ->take($limit)
                    ->get()
            );
        });
    }

    /**
     * Get related posts based on tags or categories (for news)
     *
     * @param int $limit
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getRelated($limit = 5)
    {
        $cacheKey = "related_news_{$this->id}";

        return ContentMetaDataResource::collection(
            Cache::remember($cacheKey, now()->addMinutes(10), function () use ($limit) {
                $relatedNews = collect();

                // Step 1: Fetch related news by tags (if any)
                if ($this->tags()->exists()) {
                    $tagIds = $this->tags->pluck('id')->toArray();

                    $tagResults = Cache::remember("related_news_tags_{$this->id}", now()->addMinutes(10), function () use ($tagIds, $limit, $relatedNews) {
                        return $this->whereHas('tags', function ($query) use ($tagIds) {
                            $query->whereIn('tags.id', $tagIds);
                        })
                            ->where('id', '!=', $this->id)
                            ->latest()
                            ->take($limit - $relatedNews->count())
                            ->get();
                    });

                    $relatedNews = $relatedNews->merge($tagResults);
                }
                //HERREEEE
                // If we have enough results, return them
                if ($relatedNews->count() >= $limit) {
                    return $relatedNews->take($limit);
                }

                // Step 2: Fetch related news by categories (if any)
                if ($this instanceof News) {
                    if ($this->categories()->exists()) {
                        $categoryIds = $this->categories->pluck('id')->toArray();

                        $categoryResults = Cache::remember("related_news_categories_{$this->id}", now()->addMinutes(10), function () use ($categoryIds, $limit, $relatedNews) {
                            return $this->whereHas('categories', function ($query) use ($categoryIds) {
                                $query->whereIn('categories.id', $categoryIds);
                            })
                                ->where('id', '!=', $this->id)
                                ->orderBy('id', 'DESC')
                                ->take($limit - $relatedNews->count())
                                ->get();
                        });

                        $relatedNews = $relatedNews->merge($categoryResults);
                    }
                }

                // Return exactly $limit posts
                return $relatedNews->take($limit);
            })
        );
    }

    /**
     * Get latest posts for the model
     *
     * @param int $limit
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public static function getLatest($limit = 5)
    {
        $modelName = class_basename(static::class);
        $cacheKey = strtolower($modelName) . '_latest_posts_' . $limit;

        return Cache::remember($cacheKey, now()->addMinutes(60), function () use ($limit) {
            return ContentMetaDataResource::collection(
                self::orderBy('id', 'DESC')->take($limit)->get()
            );
        });
    }

    public static function getByCategory(string $category_id, int $take = 5)
    {
        return ContentMetaDataResource::collection(
            Category::find($category_id)->news()->orderBy('id', 'DESC')->take($take)->get()
        );
    }

    public function getSeoData(): array
    {
        try {
            $published_time = !empty($this->publish_at)
                ? Carbon::parse($this->publish_at)->toIso8601String()
                : null;
        } catch (\Throwable $e) {
            $published_time = null;
        }

        try {
            $modified_time = !empty($this->updated_at)
                ? Carbon::parse($this->updated_at)->toIso8601String()
                : null;
        } catch (\Throwable $e) {
            $modified_time = null;
        }

        return [
            'title' => $this->seo_title ?? $this->title,
            'description' => $this->meta_desc ?? $this->short_description ?? '',
            'type' => 'article',
            'image' => $this->getImageUrl(),
            'url' => $this->getUrl(),
            'site_name' => setting('general.fa_brand_name') ?? 'گروه رسانه‌ای صبح‌ساحل',
            'locale' => 'fa_IR',
            'published_time' => $published_time,
            'modified_time' => $modified_time,
        ];
    }

    public function getWebsiteTitle()
    {
        $brand = setting('general.fa_brand_name') ?? 'گروه رسانه‌ای صبح‌ساحل';

        return $this->title . ' | ' . $brand;
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get image URL based on size
     *
     * @param string $size Size of image (large, medium, small)
     * @return string
     */
    public function getImageUrl(string $size = 'large')
    {
        $imageField = match ($size) {
            'large' => 'image_large',
            'medium' => 'image_medium',
            'small' => 'image_small',
            default => 'image_large'
        };

        // If requested size is not available, fallback to large
        $path = $this->{$imageField} ?? $this->image_large;

        if ($path == null){
            $path = $this->image_original;
        }

        if (!$path) {
            return 'upload/default_image.png';
        }

        // Check public disk first
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        // Then check media disk
        if (Storage::disk('media')->exists($path)) {
            return Storage::disk('media')->url($path);
        }

        // If image not found in either disk, return default
        return Storage::url('upload/default_image.png');
    }

    public function getAuthor()
    {
        return AuthorProfileResource::collection(($this->author_id == null) ? $this->user : $this->author);
    }
}
