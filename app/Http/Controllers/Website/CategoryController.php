<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContentMetaDataResource;
use App\Models\Category;
use App\Models\News;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    use \App\Http\Controllers\Website\Concerns\RendersEnglishSite;

    public function index($slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $page_title = $category->title;

        $categoryIds = $category->children->pluck('id')->prepend($category->id);

        // Get posts with pagination (don't cache paginated results)
        $posts = News::orderBy('id', 'DESC')
            ->where('lang_id', 1)
            ->where('is_published', true)
            ->whereHas('categories', function($query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            })
            ->paginate(50)
            ->withQueryString() // Maintain query parameters in pagination links
            ->through(function ($item) {
                return ContentMetaDataResource::make($item)->resolve();
            });


        $category_id = $category->id;

//        $has_follow = Auth::check()
//            ? Auth::user()->categories()->where('category_id', $category->id)->exists()
//            : false;

        $has_follow = false;

        $related =  [];

        $related_title = 'آخرین اخبار';

        $related = News::getLatest()->toArray(\request());
        $most_visited = News::getMostViewed()->toArray(\request());

        $seo = [
            'title' => $category->title,
            'description' => $category->meta_desc ?? $category->description ?? '',
            'type' => 'website',
            'image' => null,
            'url' => route('website.rtl.category', ['slug' => $category->slug]),
            'site_name' => setting('general.fa_brand_name') ?? 'گروه رسانه‌ای صبح‌ساحل',
            'locale' => 'fa_IR',
        ];

        $website_title = $category->title . ' | ' . (setting('general.fa_brand_name') ?? 'گروه رسانه‌ای صبح‌ساحل');


        return view('website.rtl.article',compact('posts','page_title','category_id','has_follow','related_title','related','most_visited','seo','website_title'));
    }

    /**
     * English category listing (en/category/{slug}) — mirrors index() with
     * English lang-filtered queries and the LTR article view (WP-15).
     */
    public function en_index($slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $page_title = $category->en_title ?: $category->title;

        try {
            $categoryIds = $category->children->pluck('id')->prepend($category->id);

            $posts = News::orderBy('id', 'DESC')
                ->where('lang_id', $this->englishLangId())
                ->where('is_published', true)
                ->whereHas('categories', function ($query) use ($categoryIds) {
                    $query->whereIn('categories.id', $categoryIds);
                })
                ->paginate(20)
                ->withQueryString()
                ->through(fn ($item) => $this->normalizeLtrItem(ContentMetaDataResource::make($item)->resolve()));

            $category_id = $category->id;

            $has_follow = false;

            $related = $this->enLatestNews(5);

            $related_title = 'Latest News';

            $most_visited = $this->enMostViewedNews();

            $website_title = $page_title . ' | ' . $this->enBrandName();

            $seo = [
                'title' => $page_title,
                'description' => $category->meta_desc ?? $category->description ?? '',
                'type' => 'website',
                'url' => url()->current(),
            ];

            return $this->ltrView('website.ltr.article', compact('posts', 'page_title', 'category_id', 'has_follow', 'related_title', 'related', 'most_visited', 'website_title', 'seo'), $page_title);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return $this->comingSoon($page_title);
        }
    }
}
