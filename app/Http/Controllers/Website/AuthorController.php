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
    public function index($user_type,$id)
    {
        if ($user_type == 'author'){
            $user = Author::findOrFail($id);

            $posts = News::where('author_id',$id)->orderBy('id','DESC')->paginate()->through(function ($item) {
                return ContentMetaDataResource::make($item)->resolve();
            });
        }elseif ($user_type == 'user'){
            $user = User::findOrFail($id);

            $posts = News::where('user_id',$id)->orderBy('id','DESC')->paginate()->through(function ($item) {
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

        return view('website.rtl.author',compact('user','posts','has_follow'));
    }

    public function en_index($id)
    {
        $user = User::findOrFail($id);

        if ($user->role_id == null){
            abort(404);
        }

        $posts = $this->getPosts($user,2);

        $user = json_decode(json_encode(AdminProfileResource::make($user)),true);

        if (Auth::check()){
            $client = Auth::user();

            $has_follow = $client->authors()->where('author_id', $id)->exists();
        }else{
            $has_follow = false;
        }

        return view('website.ltr.author',compact('user','posts','has_follow'));
    }

    private function getPosts(User $user,$lang_id = 1){
        return Post::where('user_id',$user->id)
            ->where('lang_id',$lang_id)
            ->orderBy('id','DESC')->paginate(10)->through(function ($item) {
            return $item->getPostTotallyForWebsite(1);
        });
    }
}
