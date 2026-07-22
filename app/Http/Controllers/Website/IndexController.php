<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContentMetaDataResource;
use App\Http\Resources\Website\AuthorProfileResource;
use App\Models\AppSetting;
use App\Models\Archive;
use App\Models\Author;
use App\Models\FeaturedNews;
use App\Models\Gallery;
use App\Models\News;
use App\Models\Note;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\User;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class IndexController extends Controller
{
    public function newIndex()
    {
        $cacheKey = 'website_index_page';

        $data = Cache::remember($cacheKey, now()->addMinutes(30), function () {
            $setting = [
                'rows' => [
                    'row_1' => [
                        'is_active' => true,
                        'columns' => [
                            'col_1_1' => [
                                'title' => 'سیاسی',
                                'type' => 'category',
                                'id' => 4,
                                'count' => 6
                            ],
                            'col_1_2' => [
                                'title' => 'اقتصاد',
                                'type' => 'category',
                                'id' => 9,
                                'count' => 6
                            ],
                            'col_1_3' => [
                                'title' => 'اجتماعی',
                                'type' => 'category',
                                'id' => 7,
                                'count' => 6
                            ],
                        ]
                    ],
                    'row_2' => [
                        'is_active' => true,
                        'columns' => [
                            'col_2_1' => [
                                'title' => 'فرهنگی و هنری',
                                'type' => 'category',
                                'id' => 10,
                                'count' => 6
                            ],
                            'col_2_2' => [
                                'title' => 'ورزشی',
                                'type' => 'category',
                                'id' => 1,
                                'count' => 6
                            ],
                            'col_2_3' => [
                                'title' => 'شهرستان‌ها',
                                'type' => 'category',
                                'id' => 12,
                                'count' => 6
                            ],
                        ]
                    ],
                    'row_3' => [
                        'is_active' => true,
                        'columns' => [
                            'col_3_1' => [
                                'title' => 'سیاسی',
                                'type' => 'category',
                                'id' => 2,
                                'count' => 4
                            ],
                        ]
                    ],
                ],
                'photos' => [
                    'is_active' => true,
                ],
                'video' => [
                    'is_active' => true,
                ],
                'top_row' => [
                    'is_active' => true,
                ],
            ];

            $lang_id = 1;
            $posts = [];

            $pdf = json_decode(json_encode(Archive::getLatest()),true);
  
            $notes = Note::getLatest(12)->resolve();
            $notes = collect($notes)->map(function ($note) {
                return array_merge($note, [
                    'author' => AuthorProfileResource::make(($note['author_id'] == null) ? User::find($note['user_id']) : Author::find($note['author_id']))->resolve()
                ]);
            })->toArray();

            $podcast = Podcast::getLatest()->resolve();
            $latest = News::getLatest(30)->resolve();

            $top_slider = ContentMetaDataResource::collection(FeaturedNews::getNewsByBoxTitle('top_slider'))->toArray(\request());
            $three_post = ContentMetaDataResource::collection(FeaturedNews::getNewsByBoxTitle('top_slider'))->toArray(\request());

            $compact = ['setting', 'posts', 'pdf', 'latest', 'notes', 'podcast','top_slider','three_post'];

            foreach ($setting['rows'] as $row) {
                if (!$row['is_active']) {
                    continue;
                }
                foreach ($row['columns'] as $name => $value) {
                    if ($value['type'] == 'category') {
                        $posts[$name] = News::getByCategory($value['id'], $value['count'])->resolve();
                    }
                }
            }

            if ($setting['photos']['is_active']) {
                $photos = Gallery::getLatest(11)->resolve();
                $compact = array_merge($compact, ['photos']);
            }

            if ($setting['video']['is_active']) {
                $video = Video::getLatest(6)->resolve();
                $compact = array_merge($compact, ['video']);
            }

            if ($setting['top_row']['is_active']) {
                $podcast = Podcast::getLatest(1)->resolve();
                $compact = array_merge($compact, ['notes', 'podcast']);
            }

            return compact($compact);
        });


        return view('website.rtl.index-oh', $data);
    }

    public function enIndex()
    {
        $lang_id = 2;

        $setting = AppSetting::getFirstPageItems($lang_id);

        $posts = [];

        $pdf = $this->getPost('archive',null,$lang_id);

        $latest = $this->getLatest('news',$lang_id,9);

        $compact = ['setting', 'posts', 'pdf', 'latest'];

        foreach ($setting['rows'] as $row) {
            if (!$row['is_active']) {
                continue;
            }

            foreach ($row['columns'] as $name => $value) {
                if ($value['type'] == 'category') {
                    $posts[$name] = $this->getPost('news', $value['id'], $lang_id, $value['count']);
                }
            }
        }

        if ($setting['photos']['is_active']) {
            $photos = $this->getPost('image',null,2);

            $compact = array_merge($compact,['photos']);
        }

        if ($setting['video']['is_active']) {
            $video = $this->getPost('video',null,2);

            $compact = array_merge($compact,['video']);
        }


        if ($setting['top_row']['is_active']) {
            $notes = $this->getPost('note',null,$lang_id,11);

            $podcast = $this->getPost('podcast',null,$lang_id,1);

            $compact = array_merge($compact,['notes','podcast']);
        }

        return view('website.ltr.index', compact($compact));
    }

    private function getPost($post_type, $category_id = null, $lang_id = 1, $count = 6)
    {
        if ($category_id != null){
            $query = News::orderBy('id', 'DESC')->where('lang_id', $lang_id)->whereHas('categories', function ($query) use ($category_id) {
                $query->where('categories.id', $category_id);
            });
        }else{
            $query = News::orderBy('id', 'DESC')
                ->where('lang_id', $lang_id)->where('post_type', $post_type);
        }

        return $query->limit($count)
            ->get()
            ->map(function ($item) {
                return $item->getPostTotallyForWebsite(1);
            });
    }

    private function getLatest($post_type, $lang_id = 1, $count = 6){
        return News::getLatestPosts($count,$lang_id,$post_type)->map(function ($item) {
            return $item->getPostTotallyForWebsite(1);
        });
    }

    public function advertise_click($id)
    {

    }
}
