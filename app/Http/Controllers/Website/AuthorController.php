<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Resources\admin\User\AdminProfileResource;
use App\Http\Resources\ContentMetaDataResource;
use App\Http\Resources\Website\AuthorProfileResource;
use App\Models\Author;
use App\Models\News;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthorController extends Controller
{
    use \App\Http\Controllers\Website\Concerns\RendersEnglishSite;

    public function index($user_type,$id)
    {
        if ($user_type == 'author'){
            $user = Author::findOrFail($id);

            $posts = News::where('author_id',$id)->where('is_published', true)->orderBy('id','DESC')->paginate()->through(function ($item) {
                return ContentMetaDataResource::make($item)->resolve();
            });
        }elseif ($user_type == 'user'){
            $user = User::findOrFail($id);

            $posts = News::where('user_id',$id)->where('is_published', true)->orderBy('id','DESC')->paginate()->through(function ($item) {
                return ContentMetaDataResource::make($item)->resolve();
            });
        }else{
            abort(404);
        }

        $user = AuthorProfileResource::make($user)->resolve();

        if (Auth::check()){
            $client = Auth::user();

//            $has_follow = $client->authors()->where('author_id', $id)->exists();
            $has_follow = true;
        }else{
            $has_follow = false;
        }

        $author_name = trim((string) ($user['name'] ?? ''));

        $website_title = ($author_name !== '' ? $author_name . ' | ' : '') . (setting('general.fa_brand_name') ?? 'گروه رسانه‌ای صبح‌ساحل');

        $seo = [
            'title' => $author_name !== '' ? $author_name : 'نویسنده',
            'description' => trim((string) ($user['bio'] ?? '')) ?: ('آخرین مطالب ' . $author_name . ' در پایگاه خبری صبح ساحل'),
            'type' => 'profile',
            'image' => $user['avatar'] ?? null,
            'url' => route('website.rtl.author', ['user_type' => $user_type, 'id' => $id]),
        ];

        return view('website.rtl.author',compact('user','posts','has_follow','website_title','seo'));
    }

    /**
     * English author page (en/author/{id}) — mirrors index() (user branch)
     * with English lang-filtered posts and the LTR author view (WP-15).
     */
    public function en_index($id)
    {
        $user = User::findOrFail($id);

        try {
            $posts = News::where('user_id', $id)
                ->where('lang_id', $this->englishLangId())
                ->where('is_published', true)
                ->orderBy('id', 'DESC')
                ->paginate(10)
                ->through(fn ($item) => $this->normalizeLtrItem(ContentMetaDataResource::make($item)->resolve()));

            $user = AuthorProfileResource::make($user)->resolve() + [
                'avatar' => '/asset/img/user05.png',
                'first_name' => '',
                'last_name' => '',
                'nik_name' => '',
            ];

            $has_follow = false;

            $author_name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: trim((string) ($user['name'] ?? ''));

            $website_title = ($author_name !== '' ? $author_name . ' | ' : '') . $this->enBrandName();

            $seo = [
                'title' => $author_name !== '' ? $author_name : 'Author',
                'description' => trim((string) ($user['bio'] ?? '')) ?: ('Latest articles by ' . $author_name . ' on Sobhe Sahel'),
                'type' => 'profile',
                'url' => url()->current(),
            ];

            return $this->ltrView('website.ltr.author', compact('user', 'posts', 'has_follow', 'website_title', 'seo'), 'Author');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return $this->comingSoon('Author');
        }
    }
}
