<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContentMetaDataResource;
use App\Http\Resources\Website\AuthorProfileResource;
use App\Models\AppSetting;
use App\Models\Archive;
use App\Models\Author;
use App\Models\Category;
use App\Models\FeaturedNews;
use App\Models\Gallery;
use App\Models\HomeBox;
use App\Models\News;
use App\Models\Note;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\User;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class IndexController extends Controller
{
    use \App\Http\Controllers\Website\Concerns\RendersEnglishSite;

    public function newIndex()
    {
        $cacheKey = 'website_index_page';

        $data = Cache::remember($cacheKey, now()->addMinutes(30), function () {
            // Admin-defined home boxes override the default layout when at
            // least one active box exists; otherwise the hardcoded default
            // layout below is used, so the site looks identical until admins
            // configure boxes.
            $setting = $this->buildSettingFromHomeBoxes();

            $setting ??= [
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
                        $posts[$name] = $this->getBoxColumnPosts($value);
                    }
                }
            }

            if ($setting['photos']['is_active']) {
                $photos = $this->getBoxMediaPosts(Gallery::class, $setting['photos'], 11);
                $compact = array_merge($compact, ['photos']);
            }

            if ($setting['video']['is_active']) {
                $video = $this->getBoxMediaPosts(Video::class, $setting['video'], 6);
                $compact = array_merge($compact, ['video']);
            }

            if ($setting['top_row']['is_active']) {
                if (isset($setting['top_row']['count'])) {
                    $notes = Note::getLatest($setting['top_row']['count'])->resolve();
                    $notes = collect($notes)->map(function ($note) {
                        return array_merge($note, [
                            'author' => AuthorProfileResource::make(($note['author_id'] == null) ? User::find($note['user_id']) : Author::find($note['author_id']))->resolve()
                        ]);
                    })->toArray();
                }

                $podcast = Podcast::getLatest(1)->resolve();
                $compact = array_merge($compact, ['notes', 'podcast']);
            }

            return compact($compact);
        });


        return view('website.rtl.index-oh', $data);
    }

    /**
     * Build the home page $setting array from admin-defined HomeBox rows.
     *
     * Returns null when the table does not exist, no active box is defined,
     * or anything fails — the caller then falls back to the hardcoded
     * default layout unchanged.
     */
    private function buildSettingFromHomeBoxes(): ?array
    {
        try {
            if (!Schema::hasTable('home_boxes')) {
                return null;
            }

            $boxes = HomeBox::where('is_active', true)->orderBy('sort_order')->get();

            if ($boxes->isEmpty()) {
                return null;
            }

            $setting = [
                'rows' => [
                    'row_1' => ['is_active' => false, 'columns' => []],
                    'row_2' => ['is_active' => false, 'columns' => []],
                    'row_3' => ['is_active' => false, 'columns' => []],
                ],
                'photos' => ['is_active' => false],
                'video' => ['is_active' => false],
                'top_row' => ['is_active' => false],
            ];

            // The blade renders at most two multi-column category rows
            // (row_1, row_2) and one wide single-category row (row_3).
            $categoryRowSlots = ['row_1', 'row_2'];

            foreach ($boxes as $box) {
                switch ($box->box_type) {
                    case 'category_row':
                        $slot = array_shift($categoryRowSlots);

                        if ($slot === null) {
                            break;
                        }

                        $columns = [];

                        foreach (array_slice(array_values((array) $box->category_ids), 0, 3) as $index => $categoryId) {
                            $columns[$slot . '_col_' . ($index + 1)] = [
                                'title' => Category::find($categoryId)?->title ?? $box->title,
                                'type' => 'category',
                                'id' => (int) $categoryId,
                                'count' => (int) ($box->items_count ?: 6),
                                'lead_chars' => $box->lead_chars,
                                'days' => $box->time_range_days,
                                'content_type' => $box->content_type ?: 'news',
                            ];
                        }

                        if (count($columns) > 0) {
                            $setting['rows'][$slot] = [
                                'is_active' => true,
                                'title' => $box->title,
                                'columns' => $columns,
                            ];
                        }
                        break;

                    case 'wide_category':
                        $categoryIds = array_values((array) $box->category_ids);

                        if (empty($categoryIds)) {
                            break;
                        }

                        $setting['rows']['row_3'] = [
                            'is_active' => true,
                            'title' => $box->title,
                            'columns' => [
                                'row_3_col_1' => [
                                    'title' => $box->title,
                                    'type' => 'category',
                                    'id' => (int) $categoryIds[0],
                                    'count' => (int) ($box->items_count ?: 4),
                                    'lead_chars' => $box->lead_chars,
                                    'days' => $box->time_range_days,
                                    'content_type' => $box->content_type ?: 'news',
                                ],
                            ],
                        ];
                        break;

                    case 'photos':
                        $setting['photos'] = [
                            'is_active' => true,
                            'title' => $box->title,
                            'count' => (int) ($box->items_count ?: 11),
                            'lead_chars' => $box->lead_chars,
                            'days' => $box->time_range_days,
                        ];
                        break;

                    case 'video':
                        $setting['video'] = [
                            'is_active' => true,
                            'title' => $box->title,
                            'count' => (int) ($box->items_count ?: 6),
                            'lead_chars' => $box->lead_chars,
                            'days' => $box->time_range_days,
                        ];
                        break;

                    case 'top_row':
                        $setting['top_row'] = [
                            'is_active' => true,
                            'title' => $box->title,
                            'count' => (int) ($box->items_count ?: 12),
                        ];
                        break;
                }
            }

            return $setting;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Fetch posts for one category column of the home page.
     *
     * Columns coming from the hardcoded default layout carry no extra keys
     * and keep the original News::getByCategory behavior untouched. Columns
     * built from HomeBox rows additionally honor content type, time range
     * (publish_at >= now() - days) and lead character truncation.
     */
    private function getBoxColumnPosts(array $column): array
    {
        $count = (int) ($column['count'] ?? 6);
        $contentType = $column['content_type'] ?? null;
        $days = $column['days'] ?? null;
        $leadChars = $column['lead_chars'] ?? null;

        if ($contentType === null && $days === null && $leadChars === null) {
            return News::getByCategory($column['id'], $count)->resolve();
        }

        $modelMap = [
            'note' => Note::class,
            'video' => Video::class,
            'podcast' => Podcast::class,
            'photo' => Gallery::class,
        ];

        if (isset($modelMap[$contentType])) {
            // Only news posts carry category relations; other content types
            // simply show their latest items.
            $query = $modelMap[$contentType]::orderBy('id', 'DESC');
        } else {
            $query = News::orderBy('id', 'DESC')->whereHas('categories', function ($q) use ($column) {
                $q->where('categories.id', $column['id']);
            });
        }

        if (!empty($days)) {
            $query->where('publish_at', '>=', now()->subDays((int) $days));
        }

        $items = ContentMetaDataResource::collection($query->take($count)->get())->resolve();

        return $this->applyLeadChars($items, $leadChars);
    }

    /**
     * Fetch latest items for a media (photos/video) home box, honoring the
     * configured count, time range and lead character truncation. Default
     * (hardcoded) boxes carry no extra keys and behave exactly as before.
     */
    private function getBoxMediaPosts(string $modelClass, array $box, int $defaultCount): array
    {
        $count = (int) ($box['count'] ?? $defaultCount);
        $days = $box['days'] ?? null;

        if (empty($days)) {
            $items = $modelClass::getLatest($count)->resolve();
        } else {
            $items = ContentMetaDataResource::collection(
                $modelClass::orderBy('id', 'DESC')
                    ->where('publish_at', '>=', now()->subDays((int) $days))
                    ->take($count)
                    ->get()
            )->resolve();
        }

        return $this->applyLeadChars($items, $box['lead_chars'] ?? null);
    }

    /**
     * Truncate the short description (lead) of resolved items to the given
     * number of characters. A null/zero limit leaves items untouched.
     */
    private function applyLeadChars(array $items, $leadChars): array
    {
        $leadChars = (int) ($leadChars ?? 0);

        if ($leadChars <= 0) {
            return $items;
        }

        return array_map(function ($item) use ($leadChars) {
            if (is_array($item) && isset($item['short_description'])) {
                $item['short_description'] = Str::limit(
                    trim(strip_tags((string) $item['short_description'])),
                    $leadChars
                );
            }

            return $item;
        }, $items);
    }

    /**
     * English home page (en) — mirrors newIndex() with English lang-filtered
     * queries and the LTR home view. Any failure renders the clean English
     * "coming soon" page instead of a 500 (WP-15 — چندزبانه).
     */
    public function enIndex()
    {
        try {
            $lang_id = \App\Models\Language::contentLangId('en');

            // Same default layout as the RTL home, with English column titles.
            $setting = [
                'rows' => [
                    'row_1' => [
                        'is_active' => true,
                        'columns' => [
                            'col_1_1' => ['title' => $this->enCategoryTitle(4, 'Politics'), 'type' => 'category', 'id' => 4, 'count' => 6],
                            'col_1_2' => ['title' => $this->enCategoryTitle(9, 'Economy'), 'type' => 'category', 'id' => 9, 'count' => 6],
                            'col_1_3' => ['title' => $this->enCategoryTitle(7, 'Society'), 'type' => 'category', 'id' => 7, 'count' => 6],
                        ],
                    ],
                    'row_2' => [
                        'is_active' => true,
                        'columns' => [
                            'col_2_1' => ['title' => $this->enCategoryTitle(10, 'Culture & Art'), 'type' => 'category', 'id' => 10, 'count' => 6],
                            'col_2_2' => ['title' => $this->enCategoryTitle(1, 'Sports'), 'type' => 'category', 'id' => 1, 'count' => 6],
                            'col_2_3' => ['title' => $this->enCategoryTitle(12, 'Counties'), 'type' => 'category', 'id' => 12, 'count' => 6],
                        ],
                    ],
                    'row_3' => [
                        'is_active' => true,
                        'columns' => [
                            'col_3_1' => ['title' => $this->enCategoryTitle(2, 'News'), 'type' => 'category', 'id' => 2, 'count' => 4],
                        ],
                    ],
                ],
                'photos' => ['is_active' => true],
                'video' => ['is_active' => true],
                'top_row' => ['is_active' => true],
            ];

            $posts = [];

            foreach ($setting['rows'] as $row) {
                foreach ($row['columns'] as $name => $value) {
                    $posts[$name] = collect($this->getEnPosts(News::class, $lang_id, $value['count'], $value['id']));
                }
            }

            $pdf = Archive::where('lang_id', $lang_id)
                ->where('is_published', true)
                ->orderBy('id', 'DESC')
                ->take(5)
                ->get()
                ->map(fn ($item) => [
                    'image_large' => $item->getImageUrl(),
                    'archive_number' => $item->archive_number,
                    'archive_category' => $item->archive_category->slug ?? '',
                ])
                ->all();

            $latest = collect($this->getEnPosts(News::class, $lang_id, 9));

            $notes = collect($this->getEnPosts(Note::class, $lang_id, 11))->map(function ($note) {
                $author = null;

                try {
                    $author = AuthorProfileResource::make(
                        ($note['author_id'] ?? null) === null ? User::find($note['user_id'] ?? null) : Author::find($note['author_id'])
                    )->resolve();
                } catch (\Throwable $e) {
                    // keep defaults below
                }

                $note['author'] = ($author ?: []) + [
                    'avatar' => '/asset/img/user05.png',
                    'first_name' => '',
                    'last_name' => '',
                    'nik_name' => '',
                ];

                return $note;
            });

            $podcast = collect($this->getEnPosts(Podcast::class, $lang_id, 1));

            $photos = collect($this->getEnPosts(Gallery::class, $lang_id, 11));

            $video = collect($this->getEnPosts(Video::class, $lang_id, 6))->map(function ($item) {
                $item['video'] = ['video' => $item['embed'] ?? ''];

                return $item;
            });

            $website_title = $this->enBrandName();

            return $this->ltrView('website.ltr.index', compact(
                'setting', 'posts', 'pdf', 'latest', 'notes', 'podcast', 'photos', 'video', 'website_title'
            ));
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return $this->comingSoon();
        }
    }

    /**
     * English title of a category (falls back to the given English label).
     */
    private function enCategoryTitle(int $id, string $fallback): string
    {
        try {
            $category = Category::find($id);

            return $category?->en_title ?: ($category?->title ?: $fallback);
        } catch (\Throwable $e) {
            return $fallback;
        }
    }

    /**
     * Published English rows of a content model as LTR-ready arrays
     * (optionally limited to one category — News only).
     */
    private function getEnPosts(string $modelClass, int $lang_id, int $count, ?int $category_id = null): array
    {
        $query = $modelClass::orderBy('id', 'DESC')
            ->where('lang_id', $lang_id)
            ->where('is_published', true);

        if ($category_id !== null && $modelClass === News::class) {
            $query->whereHas('categories', function ($q) use ($category_id) {
                $q->where('categories.id', $category_id);
            });
        }

        return $this->normalizeLtrItems(
            ContentMetaDataResource::collection($query->take($count)->get())->resolve()
        );
    }

    public function advertise_click($id)
    {

    }
}
