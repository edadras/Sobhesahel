<?php

namespace App\Http\Controllers\Website;

use App\Filament\Resources\NewsResource;
use App\Helpers\ResponseHelper;
use App\Helpers\TimeHelpers;
use App\Http\Controllers\Controller;
use App\Http\Resources\admin\User\AdminProfileResource;
use App\Http\Resources\ContentMetaDataResource;
use App\Http\Resources\Website\AuthorProfileResource;
use App\Http\Resources\website\AuthorRerource;
use App\Models\Advertise;
use App\Models\AppSetting;
use App\Models\Comment;
use App\Models\News;
use App\Models\Podcast;
use App\Models\Poll;
use App\Models\Post;
use App\Models\PostTranslation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Morilog\Jalali\Jalalian;

class PostController extends Controller
{
    use \App\Http\Controllers\Website\Concerns\RendersEnglishSite;

    //New
    public function new_single($type,$code,$slug)
    {
        $type = $type == 'photo' ? 'image' : $type;

        $modelMap = [
            'news' => \App\Models\News::class,
            'note' => \App\Models\Note::class,
            'video' => \App\Models\Video::class,
            'podcast' => \App\Models\Podcast::class,
            'image' => \App\Models\Gallery::class,
            'archive' => \App\Models\Archive::class,
        ];

        // Validate if type exists in the map
        if (!isset($modelMap[$type])) {
            abort(404);
        }

        // Get the model class
        $modelClass = $modelMap[$type];

        // Fetch the post
        $post = $modelClass::findOrFail($code);

        if (!$post->is_published){
            abort(404);
        }

        // Increment visit count
//        $post->increment('visits');

        $post->posted_at_jalali = Jalalian::fromCarbon(Carbon::parse($post->publish_at))->format('%d %B %Y H:i');

//        $most_visited = News::getMostViewed(5)->toArray(request());

//        $related = $post->getRelated(5)->toArray(request());

        $latest = News::getLatest(15)->toArray(request());
        // Prepare compact data
        $compact = ['post', 'seo' ,'latest','type'];

        // اخبار مرتبط هوشمند — News only: shared tags first, then same
        // category (News::relatedSmart via the News getRelated override).
        if ($type === 'news') {
            try {
                $related = $post->getRelated(5)->toArray(request());

                if (!empty($related)) {
                    $related_title = 'اخبار مرتبط';

                    $compact = array_merge($compact, ['related', 'related_title']);
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        if ($type == 'podcast'){
            $episodes = $post->getLatest()->resolve();

            $compact = array_merge($compact,['episodes']);
        }

        if ($post->author_id != null){
            $author = AuthorProfileResource::make($post->author)->resolve();

            $post->author = $author;
        }else{
//                try {
            $author = AuthorProfileResource::make($post->user)->resolve();

            $post->author = $author;
//                }catch (\Exception $e){
//                    dd($post->user);
//                }
        }

        $compact = array_merge($compact,['author']);

        if (in_array($type,['note','news'])){
//            $comments = $post->comments;

            $comments = [];

            $compact = array_merge($compact,['comments']);

        }

        $seo = $post->getSeoData();

        $website_title = $post->getWebsiteTitle();

        $compact = array_merge($compact,['seo','website_title','poll']);

        $poll = Poll::getActivePoll();

        return view("website.rtl.{$type}_single", compact($compact));
    }

    //Index
    public function index($type)
    {
        if ($type == 'photo'){
            $type = 'image';
        }

        $modelMap = [
            'news' => \App\Models\News::class,
            'note' => \App\Models\Note::class,
            'video' => \App\Models\Video::class,
            'podcast' => \App\Models\Podcast::class,
            'image' => \App\Models\Gallery::class,
        ];

        if (!isset($modelMap[$type])) {
            abort(404);
        }

        $modelClass = $modelMap[$type];

        $posts = $modelClass::orderBy('id', 'DESC')
            ->where('lang_id', 1)
            ->where('is_published', true)
            ->paginate($type == 'podcast' ? 5 : 50)
            ->through(fn($item) => ContentMetaDataResource::make($item)->resolve());

        $page_title = $this->getPageTitle($type);
        $website_title = $page_title . ' - ' . setting('general.fa_brand_name');

        $view_name = $type == 'podcast' ? "website.rtl.podcast" : "website.rtl.article";

        // Get most visited posts
        $most_visited = News::getMostViewed()->toArray(\request());

        $related = News::getLatest()->toArray(\request());

        $related_title =  'آخرین اخبار';

        $poll = Poll::getActivePoll();

        $seo = [
            'title' => $page_title,
            'description' => 'آرشیو ' . $page_title . ' پایگاه خبری صبح ساحل؛ جدیدترین مطالب ' . $page_title . ' هرمزگان و ایران',
            'type' => 'website',
            'url' => url()->current(),
        ];

        return view($view_name, compact('posts', 'page_title', 'website_title', 'most_visited', 'related', 'related_title', 'poll', 'seo'));
    }

    /**
     * English content listing (en/{type}) — mirrors index() with English
     * lang-filtered queries and LTR views (WP-15 — چندزبانه).
     */
    public function en_index($type)
    {
        if ($type == 'photo') {
            $type = 'image';
        }

        $modelMap = [
            'news' => \App\Models\News::class,
            'note' => \App\Models\Note::class,
            'video' => \App\Models\Video::class,
            'podcast' => \App\Models\Podcast::class,
            'image' => \App\Models\Gallery::class,
        ];

        if (!isset($modelMap[$type])) {
            abort(404);
        }

        $page_title = $this->getEnPageTitle($type);

        try {
            $modelClass = $modelMap[$type];

            $posts = $modelClass::orderBy('id', 'DESC')
                ->where('lang_id', $this->englishLangId())
                ->where('is_published', true)
                ->paginate($type == 'podcast' ? 5 : 50)
                ->through(fn ($item) => $this->normalizeLtrItem(ContentMetaDataResource::make($item)->resolve()));

            $website_title = $page_title . ' - ' . $this->enBrandName();

            $view_name = $type == 'podcast' ? 'website.ltr.podcast' : 'website.ltr.article';

            $most_visited = $this->enMostViewedNews();

            $related = $this->enLatestNews();

            $related_title = 'Latest News';

            $seo = [
                'title' => $page_title,
                'description' => $page_title . ' archive of Sobhe Sahel news website',
                'type' => 'website',
                'url' => url()->current(),
            ];

            return $this->ltrView($view_name, compact('posts', 'page_title', 'website_title', 'most_visited', 'related', 'related_title', 'seo'), $page_title);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return $this->comingSoon($page_title);
        }
    }

    private function getPageTitle($type)
    {
        return match ($type) {
            'news' => 'اخبار',
            'note' => 'یادداشت ها',
            'image' => 'تصاویر',
            'video' => 'ویدئو',
            'podcast' => 'پادکست',
            default => '',
        };

    }

    private function getEnPageTitle($type)
    {
        return match ($type) {
            'news' => 'News',
            'note' => 'Notes',
            'image' => 'Photos',
            'video' => 'Videos',
            'podcast' => 'Podcasts',
            default => '',
        };

    }

    //Single
    public function single($type, $code, $slug)
    {
        $post_model = Post::where('post_code', $code)->where('post_type',($type == 'photo') ? 'image' : $type)->firstOrFail()->load('PostTranslation','User');

        $post_translation = $post_model->PostTranslation[0];

        if ($post_translation == null) {
            abort(404);
        }

        $post = $post_model->getPostTotallyForWebsite(1,false,true,true);

        $post_model->increment('visits');

        $website_title = $post['title'] . ' - ' . AppSetting::get_setting('fa_page_title');

        $is_marked = User::isPostMarked($post_model->id);

        $seo = [
            'title' => $post['title'],
            'description' => strip_tags($post['short_description']),
            'keywords' => implode(',', array_map(function ($item) {
                return $item['name'];
            }, $post['tags'])),
            'image' => asset($post['image_large'])
        ];

        $compact = ['post', 'website_title', 'is_marked', 'seo'];

        //Note AND News
        if (in_array($post_translation->post_type, ['note', 'news'])) {
            $comments = $this->getComments($post_translation->post_id);

            $related = $this->getRelated($post_model);

            $latest = Post::getLatestPostsForWebsite($post_translation->post_type);

            $author = $post_model->User;

//            $author = [];

            $most_visited = Post::getMostVisited(1,null,3)->map(function ($item) {
                return $item->getPostTotallyForWebsite(1);
            });

            $compact = array_merge($compact, ['comments', 'related', 'latest', 'author','most_visited']);
        }

        //Video
        if ($post_translation->post_type == 'video') {
            $video = $post_translation->Post->PostData()->where('title', 'video')->first();

            $video = ($video == null) ? [] : $video->data;

            $compact = array_merge($compact, ['video']);
        }

        //Image
        if ($post_translation->post_type == 'image') {
            $gallery = $post_translation->Post->PostData()->where('title', 'gallery')->first();

            $gallery = ($gallery == null) ? [] : $gallery->data;

            $compact = array_merge($compact, ['gallery']);
        }

        //Podcast
        if ($post_translation->post_type == 'podcast') {
            $episodes = Post::where('id', '!=', $post_translation->post_id)->where('post_type','podcast')->limit(5)->get()->map(function ($item) {
                return $item->getPostTotallyForWebsite(1);
            });

            $compact = array_merge($compact, ['episodes']);
        }

        $poll = Poll::getActivePoll(1);

        $compact = array_merge($compact, ['poll']);

        return view('website.rtl.' . $post_translation->post_type . '_single', compact($compact));
    }

    /**
     * English single content page (en/{type}/{code}/{slug}) — mirrors
     * new_single() with an English lang_id guard and LTR views. Anything the
     * legacy LTR blades cannot render falls back to the "coming soon" page
     * (WP-15 — چندزبانه).
     */
    public function en_single($type, $code, $slug)
    {
        $type = $type == 'photo' ? 'image' : $type;

        $modelMap = [
            'news' => \App\Models\News::class,
            'note' => \App\Models\Note::class,
            'video' => \App\Models\Video::class,
            'podcast' => \App\Models\Podcast::class,
            'image' => \App\Models\Gallery::class,
        ];

        if (!isset($modelMap[$type])) {
            abort(404);
        }

        $model = $modelMap[$type]::findOrFail($code);

        // Per-content language guard: only English rows are served here.
        if (!$model->is_published || (int) $model->lang_id !== $this->englishLangId()) {
            abort(404);
        }

        try {
            $post = $this->normalizeLtrItem(ContentMetaDataResource::make($model)->resolve());

            $post['post_body'] = $post['body'] ?? '';

            $post['category'] = [];

            if ($model instanceof News) {
                $post['category'] = $model->categories
                    ->map(fn ($category) => [
                        'slug' => $category->slug,
                        'en_name' => $category->en_title ?: $category->title,
                    ])
                    ->values()
                    ->all();
            }

            try {
                $post['tags'] = $model->tags->map(fn ($tag) => ['name' => (string) $tag->name])->values()->all();
            } catch (\Throwable $e) {
                $post['tags'] = [];
            }

            $comments = [];

            $is_marked = false;

            $website_title = $post['title'] . ' - ' . $this->enBrandName();

            $seo = [
                'title' => $post['title'],
                'description' => strip_tags((string) ($post['short_description'] ?? '')),
                'keywords' => implode(',', array_map(fn ($tag) => $tag['name'], $post['tags'])),
                'image' => asset($post['image_large']),
            ];

            $related = $this->enLatestNews(5);

            $related_title = 'Latest News';

            $most_visited = $this->enMostViewedNews();

            $poll = null;

            $data = compact('post', 'comments', 'is_marked', 'website_title', 'seo', 'related', 'related_title', 'most_visited', 'poll');

            if ($type == 'video') {
                $data['video'] = ['video' => $model->embed ?? ''];
            }

            if ($type == 'image') {
                $data['gallery'] = is_array($model->attachments ?? null) ? $model->attachments : [];
            }

            if ($type == 'podcast') {
                $data['episodes'] = $this->normalizeLtrItems(
                    ContentMetaDataResource::collection(
                        \App\Models\Podcast::where('id', '!=', $model->id)
                            ->where('lang_id', $this->englishLangId())
                            ->where('is_published', true)
                            ->orderBy('id', 'DESC')
                            ->limit(5)
                            ->get()
                    )->resolve()
                );
            }

            return $this->ltrView("website.ltr.{$type}_single", $data, $post['title']);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return $this->comingSoon();
        }
    }

    private function getComments($post_id)
    {
        return Comment::where('post_id', $post_id)->where('status', 'verified')->orderBy('id', 'DESC')->get()->map(function ($item) {
            if ($item->lang_id == 1){
                $item->date_ago = TimeHelpers::translateCarbonDiff(Carbon::parse($item->created_at));
            }else{
                Carbon::setLocale('en');

                $item->date_ago = Carbon::parse($item->created_at)->diffForHumans();
            }

            return $item;
        });
    }

    private function getRelated(Post $post,$lang_id = 1)
    {
        return $post->getRelatedPosts($lang_id)->map(function ($item) {
            return $item->getPostTotallyForWebsite(1);
        });
    }

    //Actions

    public function action(Request $request)
    {
        if (!Auth::check()) {
            abort(401);
        }

        $this->validate($request, [
            'type' => 'required',
            'id' => 'required',
        ]);

        $user = Auth::user();

        if ($request->get('type') == 'category') {
            $categoryId = $request->get('id');

            if ($user->categories()->where('category_id', $categoryId)->exists()) {
                $user->categories()->detach([$categoryId]);

                return ResponseHelper::basic_response(true, [
                    'message' => ($request->get('lang') == 'fa') ? 'دسته بندی از لیست علاقه مندی های شما حذف شد' : 'The category has been removed from your favorites list.',
                    'action' => 'unfollow'
                ]);
            } else {
                $user->categories()->attach([$categoryId]);

                return ResponseHelper::basic_response(true, [
                    'message' => ($request->get('lang') == 'fa') ? 'دسته بندی به لیست علاقه مندی های شما افزوده شد' : 'The category has been added to your favorites list.',
                    'action' => 'follow'
                ]);
            }
        }

        if ($request->get('type') == 'author') {
            $userId = $request->get('id');

            if ($user->authors()->where('author_id', $userId)->exists()) {
                $user->authors()->detach([$userId]);

                return ResponseHelper::basic_response(true, [
                    'message' => ($request->get('lang') == 'fa') ? 'نویسنده لیست علاقه مندی های شما حذف شد' : 'The author has been removed from your favorites list.',
                    'action' => 'unfollow'
                ]);
            } else {
                $user->authors()->attach([$userId]);

                return ResponseHelper::basic_response(true, [
                    'message' => ($request->get('lang') == 'fa') ? 'نویسنده به لیست علاقه مندی های شما افزوده شد' : 'The author has been added to your favorites list.',
                    'action' => 'follow'
                ]);
            }
        }

        if ($request->get('type') == 'post') {
            $postId = $request->get('id');

            if ($user->bookmarks()->where('post_id', $postId)->exists()) {
                $user->bookmarks()->detach([$postId]);

                return ResponseHelper::basic_response(true, [
                    'message' => ($request->get('lang') == 'fa') ? 'پست آنمارک شد' : 'Post unmarked',
                    'action' => 'unfollow'
                ]);
            } else {
                $user->bookmarks()->attach([$postId]);

                return ResponseHelper::basic_response(true, [
                    'message' =>  ($request->get('lang') == 'fa') ? 'پست به بوکمارک های شما افزوده شد' : 'Post marked',
                    'action' => 'follow'
                ]);
            }
        }

    }

    public function redirect($type, $code)
    {
        $type = $type === 'photo' ? 'image' : $type;

        // Map types to their respective models
        $modelMap = [
            'news' => \App\Models\News::class,
            'note' => \App\Models\Note::class,
            'video' => \App\Models\Video::class,
            'podcast' => \App\Models\Podcast::class,
            'image' => \App\Models\Gallery::class, // 'photo' is treated as 'image'
            'archive' => \App\Models\Archive::class,
        ];

        // Validate if type exists in the map
        if (!isset($modelMap[$type])) {
            abort(404);
        }

        // Get the model class
        $modelClass = $modelMap[$type];

        // Fetch the post
        $post = $modelClass::where('id', $code)->firstOrFail();

        // Redirect to the post URL
        return redirect($post->getUrl());
    }
}
