<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContentMetaDataResource;
use App\Models\Category;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Tags\Tag;

class TagController extends Controller
{
    public function index($name)
    { 
        abort(503);
        $tag = Tag::findFromString($name,null,'fa');

//        dd($tag);

        if (!$tag) {
            abort(404);
        }

        $page_title = "#$name";
        $related_title = 'آخرین اخبار';

        $models = [
            \App\Models\News::class,
//            \App\Models\Video::class,
//            \App\Models\Podcast::class,
//            \App\Models\Gallery::class,
//            \App\Models\Note::class,
        ];

        $allPosts = collect($models)
            ->flatMap(fn($model) => $model::withAnyTags([$tag->name])
                ->where('lang_id', 1)
                ->latest()
                ->get()
            )
            ->map(fn($item) => ContentMetaDataResource::make($item)->resolve());



        $perPage = 20;
        $currentPage = request()->get('page', 1);

//        $posts = News::search('')
//            ->where('tags', $tag->name)
//            ->orderBy('created_at', 'desc')
//            ->paginate($perPage)
//            ->through(fn($item) => ContentMetaDataResource::make($item)->resolve());

        $related = News::getLatest()->toArray(\request());
        $most_visited = News::getMostViewed()->toArray(\request());

        return view('website.rtl.article', compact('posts', 'page_title', 'related', 'related_title', 'most_visited'));
    }

    public function en_index($name)
    {
        $tag = Tag::where('name', $name)->where('lang_id',2)->firstOrFail();

        $page_title = "#$name";

        $related =  Post::getLatestPostsForWebsite('news',5,2);

        $related_title = 'Latest News';

        $most_visited = Post::getMostVisited(2,null,3)->map(function ($item) {
            return $item->getPostTotallyForWebsite(1);
        });

        $posts = Post::orderBy('id', 'DESC')
            ->where('lang_id', 2)
            ->whereHas('tags', function($query) use ($tag) {
                $query->where('tags.id', $tag->id);
            })
            ->paginate(20)
            ->through(function ($item) {
                return $item->getPostTotallyForWebsite(1);
            });

        return view('website.ltr.article',compact('posts','page_title','related','related_title','most_visited'));
    }
}
