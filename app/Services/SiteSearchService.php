<?php

namespace App\Services;

use App\Models\Author;
use App\Models\Gallery;
use App\Models\News;
use App\Models\Note;
use App\Models\Podcast;
use App\Models\User;
use App\Models\Video;
use App\Support\SearchIndexSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Morilog\Jalali\Jalalian;

class SiteSearchService
{
    /**
     * @param  array{category?: string, post_type?: string, from_date?: string, to_date?: string, order_type?: string, author?: string}  $options
     */
    public function paginate(string $searchTerm, array $options, int $page = 1, int $perPage = 10): LengthAwarePaginator
    {
        $searchTerm = trim($searchTerm);
        $options = $this->normalizeOptions($options);
        $orderType = strtoupper($options['order_type'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
        $modelEntries = $this->resolveModelEntries($options);

        if ($searchTerm === '') {
            return $this->paginateDatabase($modelEntries, $options, $page, $perPage, $orderType);
        }

        if ($this->shouldUseMeilisearch()) {
            try {
                $results = $this->searchMeilisearch($modelEntries, $searchTerm, $options, $orderType);

                if ($results->isNotEmpty()) {
                    return $this->paginateCollection($results, $page, $perPage);
                }
            } catch (\Throwable $exception) {
                Log::warning('Meilisearch search failed, using database fallback.', [
                    'term' => $searchTerm,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return $this->paginateDatabaseWithTerm($modelEntries, $searchTerm, $options, $page, $perPage, $orderType);
    }

    /**
     * Search authors (Author model) and journalist users (User model with published content)
     * matching the given term. Database-only, so it works even when Meilisearch is down.
     */
    public function searchAuthors(string $searchTerm, int $limit = 12): Collection
    {
        $searchTerm = trim($searchTerm);

        if ($searchTerm === '') {
            return collect();
        }

        $likeTerm = '%'.$searchTerm.'%';
        $results = collect();

        try {
            $authors = Author::query()
                ->where(function (Builder $query) use ($likeTerm) {
                    $query->where('name', 'like', $likeTerm)
                        ->orWhere('nik_name', 'like', $likeTerm);
                })
                ->orderBy('name')
                ->limit($limit)
                ->get();

            foreach ($authors as $author) {
                $results->push([
                    'type' => 'author',
                    'id' => $author->id,
                    'name' => $author->name,
                    'nik_name' => $author->nik_name ?? '',
                    'avatar' => $author->avatar ? Storage::url($author->avatar) : '/asset/img/user05.png',
                    'url' => route('website.rtl.author', ['user_type' => 'author', 'id' => $author->id]),
                ]);
            }

            $remaining = $limit - $results->count();

            if ($remaining > 0) {
                $users = User::query()
                    ->where('name', 'like', $likeTerm)
                    ->where(function (Builder $query) {
                        $query->whereHas('news', fn (Builder $newsQuery) => $newsQuery->where('status', 'published')->where('is_published', true))
                            ->orWhereHas('notes', fn (Builder $noteQuery) => $noteQuery->where('status', 'published')->where('is_published', true));
                    })
                    ->orderBy('name')
                    ->limit($remaining)
                    ->get();

                foreach ($users as $user) {
                    $results->push([
                        'type' => 'user',
                        'id' => $user->id,
                        'name' => $user->name,
                        'nik_name' => '',
                        'avatar' => $user->avatar_url ? Storage::url($user->avatar_url) : '/asset/img/user05.png',
                        'url' => route('website.rtl.author', ['user_type' => 'user', 'id' => $user->id]),
                    ]);
                }
            }
        } catch (\Throwable $exception) {
            Log::warning('Author search failed.', [
                'term' => $searchTerm,
                'message' => $exception->getMessage(),
            ]);
        }

        return $results;
    }

    private function shouldUseMeilisearch(): bool
    {
        return Cache::remember('search.meilisearch_has_docs', 120, function () {
            try {
                $raw = News::search('')->take(1)->raw();

                return (int) ($raw['estimatedTotalHits'] ?? $raw['totalHits'] ?? 0) > 0;
            } catch (\Throwable) {
                return false;
            }
        });
    }

    private function paginateCollection(Collection $results, int $page, int $perPage): LengthAwarePaginator
    {
        $total = $results->count();
        $items = $results->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }

    /**
     * @param  array<string, class-string<Model>>  $modelEntries
     * @param  array{category?: string, post_type?: string, from_date?: string, to_date?: string}  $options
     */
    private function paginateDatabase(array $modelEntries, array $options, int $page, int $perPage, string $orderType): LengthAwarePaginator
    {
        if (count($modelEntries) === 1) {
            $indexName = array_key_first($modelEntries);
            $modelClass = $modelEntries[$indexName];

            $query = $this->basePublishedQuery($modelClass, $options);
            $paginator = $query
                ->with(['author', 'user'])
                ->orderByRaw("COALESCE(publish_at, created_at) {$orderType}")
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->transformPaginator($paginator, $indexName);
        }

        $results = collect();

        foreach ($modelEntries as $indexName => $modelClass) {
            $items = $this->basePublishedQuery($modelClass, $options)
                ->with(['author', 'user'])
                ->orderByRaw("COALESCE(publish_at, created_at) {$orderType}")
                ->limit(500)
                ->get()
                ->map(fn (Model $item) => $this->decorateResult($item, $indexName));

            $results = $results->merge($items);
        }

        return $this->paginateCollection($this->sortResults($results, $orderType), $page, $perPage);
    }

    /**
     * @param  array<string, class-string<Model>>  $modelEntries
     * @param  array{category?: string, post_type?: string, from_date?: string, to_date?: string}  $options
     */
    private function paginateDatabaseWithTerm(
        array $modelEntries,
        string $searchTerm,
        array $options,
        int $page,
        int $perPage,
        string $orderType
    ): LengthAwarePaginator {
        if (count($modelEntries) > 1) {
            $modelEntries = ['news' => News::class];
        }

        $indexName = array_key_first($modelEntries);
        $modelClass = $modelEntries[$indexName];

        $query = $this->basePublishedQuery($modelClass, $options);
        $query = $this->applyTextSearchFilter($query, $searchTerm, $modelClass);

        $paginator = $query
            ->with(['author', 'user'])
            ->orderByRaw("COALESCE(publish_at, created_at) {$orderType}")
            ->paginate($perPage, ['*'], 'page', $page);

        return $this->transformPaginator($paginator, $indexName);
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  array{category?: string, from_date?: string, to_date?: string, author?: string}  $options
     * @return Builder<Model>
     */
    private function basePublishedQuery(string $modelClass, array $options): Builder
    {
        $query = $modelClass::query()
            ->where('status', 'published')
            ->where('is_published', true);

        $query = $this->applyCategoryFilter($query, $modelClass, $options);
        $query = $this->applyAuthorFilter($query, $options);

        return $this->applyDateFilters($query, $options);
    }

    /**
     * @param  Builder<Model>  $query
     * @param  array{author?: string}  $options
     * @return Builder<Model>
     */
    private function applyAuthorFilter(Builder $query, array $options): Builder
    {
        if (($options['author'] ?? 'all') === 'all' || ($options['author'] ?? '') === '') {
            return $query;
        }

        return $query->where('author_id', (int) $options['author']);
    }

    /**
     * @param  Builder<Model>  $query
     * @param  class-string<Model>  $modelClass
     * @return Builder<Model>
     */
    private function applyTextSearchFilter(Builder $query, string $searchTerm, string $modelClass): Builder
    {
        $table = (new $modelClass)->getTable();
        $likeTerm = '%'.$searchTerm.'%';

        return $query->where(function (Builder $searchQuery) use ($searchTerm, $likeTerm, $modelClass, $table) {
            if ($this->hasFullTextIndex($table)) {
                $searchQuery->whereRaw(
                    'MATCH(title, short_description) AGAINST(? IN BOOLEAN MODE)',
                    [$this->toFullTextBooleanTerm($searchTerm)]
                );
            } else {
                $searchQuery->where(function (Builder $likeQuery) use ($likeTerm, $modelClass) {
                    $likeQuery->where('title', 'like', $likeTerm)
                        ->orWhere('short_description', 'like', $likeTerm);

                    if ($this->modelHasSubTitle($modelClass)) {
                        $likeQuery->orWhere('sub_title', 'like', $likeTerm);
                    }

                    if ($modelClass === Note::class) {
                        $likeQuery->orWhere('seo_title', 'like', $likeTerm);
                    }
                });
            }

            if ($this->hasFullTextIndex($table) && $this->modelHasSubTitle($modelClass)) {
                $searchQuery->orWhere('sub_title', 'like', $likeTerm);
            }

            if ($this->hasFullTextIndex($table) && $modelClass === Note::class) {
                $searchQuery->orWhere('seo_title', 'like', $likeTerm);
            }

            $searchQuery->orWhereHas('tags', fn (Builder $tagQuery) => $tagQuery->where('name', 'like', $likeTerm));
            $searchQuery->orWhereHas('author', fn (Builder $authorQuery) => $authorQuery->where('name', 'like', $likeTerm));

            if ($modelClass === News::class) {
                $searchQuery->orWhereHas(
                    'categories',
                    fn (Builder $categoryQuery) => $categoryQuery->where('title', 'like', $likeTerm)
                );
            }
        });
    }

    private function toFullTextBooleanTerm(string $term): string
    {
        $parts = preg_split('/\s+/u', trim($term)) ?: [];

        return collect($parts)
            ->filter()
            ->map(fn (string $word) => '+'.str_replace(['+', '-', '*', '"', '(', ')', '<', '>', '~'], '', $word).'*')
            ->implode(' ');
    }

    private function hasFullTextIndex(string $table): bool
    {
        return Cache::remember("search.fulltext.{$table}", 3600, function () use ($table) {
            try {
                $indexes = DB::select(
                    "SHOW INDEX FROM `{$table}` WHERE Index_type = 'FULLTEXT'"
                );

                return count($indexes) > 0;
            } catch (\Throwable) {
                return false;
            }
        });
    }

    private function modelHasSubTitle(string $modelClass): bool
    {
        return in_array($modelClass, [News::class, Gallery::class, Video::class, Podcast::class], true);
    }

    /**
     * @param  array{category?: string, post_type?: string, from_date?: string, to_date?: string, order_type?: string}  $options
     * @return array<string, class-string<Model>>
     */
    private function resolveModelEntries(array $options): array
    {
        $all = SearchIndexSettings::modelsByIndex();

        if (($options['category'] ?? 'all') !== 'all') {
            return ['news' => News::class];
        }

        $postType = $options['post_type'] ?? 'all';

        if ($postType === 'all') {
            return $all;
        }

        $map = [
            'news' => ['news' => News::class],
            'gallery' => ['galleries' => Gallery::class],
            'video' => ['videos' => Video::class],
            'note' => ['notes' => Note::class],
            'podcast' => ['podcasts' => Podcast::class],
        ];

        return $map[$postType] ?? $all;
    }

    /**
     * @param  array<string, class-string<Model>>  $modelEntries
     * @param  array{category?: string, post_type?: string, from_date?: string, to_date?: string}  $options
     */
    private function searchMeilisearch(array $modelEntries, string $searchTerm, array $options, string $orderType): Collection
    {
        $results = collect();
        $filter = $this->buildMeilisearchFilter($options);

        foreach ($modelEntries as $indexName => $modelClass) {
            $indexFilter = $filter;

            if ($modelClass === News::class && ($options['category'] ?? 'all') !== 'all') {
                $indexFilter = $this->appendFilter($indexFilter, 'category_ids = '.(int) $options['category']);
            }

            if ($modelClass !== News::class && ($options['category'] ?? 'all') !== 'all') {
                continue;
            }

            try {
                $builder = $modelClass::search($searchTerm, function ($meilisearch, $query, $searchOptions) use ($indexFilter) {
                    $searchOptions['limit'] = 1000;
                    $searchOptions['attributesToHighlight'] = [
                        'title',
                        'sub_title',
                        'short_description',
                        'body',
                        'author',
                        'categories',
                        'tags',
                    ];
                    $searchOptions['attributesToCrop'] = ['body', 'short_description'];
                    $searchOptions['cropLength'] = 200;
                    $searchOptions['highlightPreTag'] = '<mark class="search-highlight">';
                    $searchOptions['highlightPostTag'] = '</mark>';

                    if ($indexFilter !== '') {
                        $searchOptions['filter'] = $indexFilter;
                    }

                    return $meilisearch->search($query, $searchOptions);
                });

                $raw = $builder->raw();
            } catch (\Throwable $exception) {
                Log::warning('Meilisearch index search failed.', [
                    'index' => $indexName,
                    'term' => $searchTerm,
                    'message' => $exception->getMessage(),
                ]);

                continue;
            }

            $hits = collect($raw['hits'] ?? []);

            if ($hits->isEmpty()) {
                continue;
            }

            $ids = $hits
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->values()
                ->all();

            $models = $modelClass::query()
                ->whereIn('id', $ids)
                ->where('status', 'published')
                ->where('is_published', true)
                ->with(['author', 'user'])
                ->get()
                ->keyBy('id');

            foreach ($hits as $hit) {
                $id = (int) ($hit['id'] ?? 0);

                if (! $models->has($id)) {
                    continue;
                }

                $model = $models->get($id);
                $formatted = $hit['_formatted'] ?? [];

                if (! empty($formatted['title'])) {
                    $model->search_title = $formatted['title'];
                }

                $model->search_snippet = $formatted['body']
                    ?? $formatted['short_description']
                    ?? $formatted['author']
                    ?? $formatted['categories']
                    ?? null;

                $results->push($this->decorateResult($model, $indexName));
            }
        }

        return $this->sortResults($results, $orderType);
    }

    private function transformPaginator(LengthAwarePaginator $paginator, string $indexName): LengthAwarePaginator
    {
        $paginator->setCollection(
            $paginator->getCollection()->map(fn (Model $item) => $this->decorateResult($item, $indexName))
        );

        return $paginator;
    }

    private function decorateResult(Model $item, string $indexName): Model
    {
        $prefix = match ($indexName) {
            'galleries' => 'gallery',
            default => rtrim($indexName, 's'),
        };

        return $item->setAttribute('unique_id', $prefix.'_'.$item->getKey());
    }

    private function sortResults(Collection $results, string $orderType): Collection
    {
        return $results
            ->sortBy(function (Model $item) use ($orderType) {
                $publishAt = $item->publish_at ?? $item->created_at ?? now();

                return $orderType === 'DESC' ? -strtotime((string) $publishAt) : strtotime((string) $publishAt);
            })
            ->values();
    }

    /**
     * @param  array{category?: string, from_date?: string, to_date?: string, author?: string}  $options
     */
    private function buildMeilisearchFilter(array $options): string
    {
        $filters = [];

        if (($options['author'] ?? 'all') !== 'all' && ($options['author'] ?? '') !== '') {
            $filters[] = 'author_id = '.(int) $options['author'];
        }

        if (! empty($options['from_date'])) {
            $from = Jalalian::fromFormat('Y/m/d', $options['from_date'])->toCarbon()->startOfDay()->format('Y-m-d H:i:s');
            $filters[] = 'publish_at >= "'.$from.'"';
        }

        if (! empty($options['to_date'])) {
            $to = Jalalian::fromFormat('Y/m/d', $options['to_date'])->toCarbon()->endOfDay()->format('Y-m-d H:i:s');
            $filters[] = 'publish_at <= "'.$to.'"';
        }

        return implode(' AND ', $filters);
    }

    private function appendFilter(string $filter, string $clause): string
    {
        return $filter === '' ? $clause : $filter.' AND '.$clause;
    }

    /**
     * @param  Builder<Model>  $query
     * @param  class-string<Model>  $modelClass
     * @param  array{category?: string}  $options
     * @return Builder<Model>
     */
    private function applyCategoryFilter(Builder $query, string $modelClass, array $options): Builder
    {
        if ($modelClass !== News::class || ($options['category'] ?? 'all') === 'all') {
            return $query;
        }

        return $query->whereHas('categories', fn ($categoryQuery) => $categoryQuery->where('categories.id', (int) $options['category']));
    }

    /**
     * @param  Builder<Model>  $query
     * @param  array{from_date?: string, to_date?: string}  $options
     * @return Builder<Model>
     */
    private function applyDateFilters(Builder $query, array $options): Builder
    {
        if (! empty($options['from_date'])) {
            $fromDate = Jalalian::fromFormat('Y/m/d', $options['from_date'])->toCarbon()->startOfDay();
            $query->where('publish_at', '>=', $fromDate);
        }

        if (! empty($options['to_date'])) {
            $toDate = Jalalian::fromFormat('Y/m/d', $options['to_date'])->toCarbon()->endOfDay();
            $query->where('publish_at', '<=', $toDate);
        }

        return $query;
    }

    /**
     * @param  array{category?: string, post_type?: string, from_date?: string, to_date?: string, order_type?: string, author?: string}  $options
     * @return array{category: string, post_type: string, from_date: string, to_date: string, order_type: string, author: string}
     */
    private function normalizeOptions(array $options): array
    {
        $postType = $options['post_type'] ?? 'all';

        if ($postType === 'image' || $postType === 'photo') {
            $postType = 'gallery';
        }

        return [
            'category' => (string) ($options['category'] ?? 'all'),
            'post_type' => (string) $postType,
            'from_date' => (string) ($options['from_date'] ?? ''),
            'to_date' => (string) ($options['to_date'] ?? ''),
            'order_type' => (string) ($options['order_type'] ?? 'DESC'),
            'author' => (string) ($options['author'] ?? 'all'),
        ];
    }
}
