<?php

namespace App\Http\Controllers\Website;

use App\Helpers\ResponseHelper;
use App\Helpers\TimeHelpers;
use App\Http\Controllers\Controller;
use App\Http\Resources\website\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use App\Models\PostTranslation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class NewsController extends Controller
{
    public function index()
    {
        $posts = Post::orderBy('id', 'DESC')
            ->where('lang_id', 1)->where('post_type', 'news')
            ->paginate(20)
            ->through(function ($item) {
                return $item->getPostTotallyForWebsite(1);
            });

        $website_title = 'اخبار | صبح ساحل';

        $page_title = 'اخبار';

        return view('website.rtl.article', compact('posts', 'page_title','website_title'));
    }

    public function comment($type,$code,$slug, Request $request)
    {
        $post = Post::where('post_code',$code)->where('type',($type == 'photo') ? 'image' : $type)->firstOrFail();

        $lang = ($post->lang_id == 1)? 'fa' : 'en';

        $this->validateComment($request,$lang);

        try {
            $comment = Comment::create([
                'post_id' => $post->post_id,
                'comment' => $request->get('text'),
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'lang_id' => $post->lang_id
            ]);

            $res = ($comment instanceof Comment);
        }catch (\Exception $e){
            $res = false;
        }

        if ($lang == 'fa'){
            $message = ($res) ? 'دیدگاه شما با موفقیت ثبت شد و در دست تایید قرار گرفت' : 'خطایی در ثبت دیدگاه شما رخ داده';
        }else{
            $message = ($res) ? 'Your comment was successfully submitted and is pending approval' : 'An error occurred while submitting your comment';
        }

        return ResponseHelper::simple_response($res,$message);
    }

    public function validateComment(Request $request,$lang)
    {
        App::setLocale($lang);

        $this->validate($request, [
            'name' => 'required|max:255',
            'email' => 'required|max:255|email',
            'text' => 'required|max:2048'
        ]);
    }
}
