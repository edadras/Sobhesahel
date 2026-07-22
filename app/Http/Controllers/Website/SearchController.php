<?php

namespace App\Http\Controllers\Website;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
//        abort(503);

        if (!$request->has('q') && $request->get('q') == '') {
            abort(404);
        }

        $posts = [];

        $count = 0;

//        $posts = $posts->paginate(12)
//            ->through(function ($item) {
//                return $item->getPostTotallyForWebsite(1);
//            });

        return view('website.rtl.search', compact('posts', 'count'));
    }

    public function en_index(Request $request)
    {
        if (!$request->has('q') && $request->get('q') == '') {
            abort(404);
        }

//        $posts = Post::search($request->get('query'));
//
//        $count = $posts->count();
//
//        $posts = $posts->paginate(12)
//            ->through(function ($item) {
//                return $item->getPostTotallyForWebsite(1);
//            });

        $posts = [];
        $count = 0;

        return view('website.ltr.search', compact('posts', 'count'));
    }

    public function api(Request $request)
    {
        if (!$request->has('q') && $request->get('q') == '') {
            abort(404);
        }

        $posts = Post::searchInTitle($request->get('q'),$request->get('lang_id'));

        if ($request->has('order_type') && in_array($request->get('order_type'),['ASC','DESC'])){
            $posts = $posts->orderBy('id',$request->get('order_type'));
        }

        if ($request->has('post_type') && in_array($request->get('post_type'),['news','video','image','podcast'])){
            $posts = $posts->where('post_type',$request->get('post_type'));
        }else{
            $posts = $posts->whereIn('post_type',['news','image','video','podcast']);
        }

        if ($request->has('category') && $request->get('category') != 'all' && $request->get('category') != ''){
            $posts = $posts->whereHas('categories',function ($q) use ($request){
                $q->where('categories.id',$request->get('category'));
            });
        }

        if ($request->has('from_date') && $request->get('from_date') != ''){
            $from_date = Carbon::createFromFormat('Y-m-d', $request->get('from_date'));

            $posts = $posts->whereDate('posted_at','>',$from_date);
        }

        if ($request->has('to_date') && $request->get('to_date') != ''){
            $to_date = Carbon::createFromFormat('Y-m-d', $request->get('to_date'));

            $posts = $posts->whereDate('posted_at','<',$to_date);
        }

        $posts = $posts->paginate(12)
            ->through(function ($item) {
                return $item->getPostTotallyForWebsite(1);
            });

        return ResponseHelper::basic_response(true, [
            'posts' => $posts,
            'categories' => Category::select(['id','fa_name'])->get()
        ]);
    }
}
