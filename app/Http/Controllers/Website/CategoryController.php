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
    public function index($slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $page_title = $category->title;

        $categoryIds = $category->children->pluck('id')->prepend($category->id);

        // Get posts with pagination (don't cache paginated results)
        $posts = News::orderBy('id', 'DESC')
            ->where('lang_id', 1)
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

    public function en_index($slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $page_title = $category->en_name;

        $categoryIds = collect([$category->id]);

        $childCategoryIds = $category->children->pluck('id');
        $categoryIds = $categoryIds->merge($childCategoryIds);

        $posts = Post::orderBy('id', 'DESC')
            ->where('lang_id', 2)
            ->whereHas('categories', function($query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            })
            ->paginate(20)
            ->through(function ($item) {
                return $item->getPostTotallyForWebsite(1);
            });

        $category_id = $category->id;

        if (Auth::check()){
            $user = Auth::user();

            $has_follow = $user->categories()->where('category_id', $category_id)->exists();
        }else{
            $has_follow = false;
        }

        $related =  Post::getLatestPostsForWebsite('news',5,2);

        $related_title = 'Latest News';

        $most_visited = Post::getMostVisited(2,null,3)->map(function ($item) {
            return $item->getPostTotallyForWebsite(1);
        });

        return view('website.ltr.article',compact('posts','page_title','category_id','has_follow','related_title','related','most_visited'));
    }
}
