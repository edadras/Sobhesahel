<?php

namespace App\Http\Controllers\Website;

use App\Constant\AppConstant;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Archive;
use App\Models\News;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArchiveController extends Controller
{
    public function index()
    {
        $posts = Archive::getLatest()->resolve();

        return view('website.rtl.archive', compact('posts'));
    }

    public function en_index()
    {
        $posts = Post::orderBy('id', 'DESC')
            ->where('lang_id', 2)->where('post_type', 'archive')
            ->paginate(12)
            ->through(function ($item) {
                return $item->getPostTotallyForWebsite(1);
            });

        return view('website.ltr.archive', compact('posts'));
    }

    public function get_data(Request $request)
    {
        $posts = Post::orderBy('id', 'DESC')
            ->where('lang_id', $request->get('lang_id'))->where('post_type', 'archive');

        if ($request->has('archive') && $request->get('archive') != 'all') {
            $posts->whereHas('PostData', function ($query) use ($request) {
                $query->where('title', 'category')->where('value', $request->get('archive'));
            });
        }

        if ($request->has('number') && $request->get('number') != '') {
            $posts->whereHas('PostData', function ($query) use ($request) {
                $query->where('title', 'number')->where('value', $request->get('number'));
            });
        }

        $posts = $posts->paginate(12)
            ->through(function ($item) {
                return $item->getPostTotallyForWebsite(1);
            });

        return ResponseHelper::basic_response(true, [
            'archives' => $posts,
            'list' => $request->get('lang_id') == 1 ? AppConstant::archives_type() : AppConstant::en_archives_type()
        ]);
    }

    public function pdf($archive, $number)
    {
        $post = Archive::whereHas('archive_category', function ($q) use ($archive) {
                $q->where('slug', $archive);
            })->where('archive_number',$number)->firstOrFail();

        $url = Storage::url($post->archive_file);

        return view('website.pages.pdf',compact('url'));
    }
}
