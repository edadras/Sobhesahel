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
    use \App\Http\Controllers\Website\Concerns\RendersEnglishSite;

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

    /**
     * English tag listing (en/tag/{name}). The RTL tag page is currently
     * disabled (index() aborts 503), so the English side renders the clean
     * "coming soon" page instead of mirroring a broken listing (WP-15).
     */
    public function en_index($name)
    {
        return $this->comingSoon("#$name");
    }
}
