<?php
/**
 * GitHub: RoyalHaze
 * Date: 5/14/25
 * Time: 6:05 PM
 **/

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\{News, Gallery, Video, Note, Category};

class SearchHelper
{
    private $perPage = 10;
    private $modelMap = [
        'news' => News::class,
        'gallery' => Gallery::class,
        'video' => Video::class,
        'note' => Note::class,
    ];

    public function getSearchResults(Request $request, string $query = '', array $options = []): LengthAwarePaginator
    {
        try {
            $currentPage = max(1, $request->get('page', 1));
            $searchTerm = $query ?: $request->input('query', '');
            $options = $this->sanitizeOptions($options + $request->only(['category', 'post_type', 'from_date', 'to_date', 'order_type']));

            if ($options['category'] !== 'all') {
                return $this->searchByCategory($searchTerm, $options);
            }

            if ($options['post_type'] !== 'all' && isset($this->modelMap[$options['post_type']])) {
                return $this->searchByPostType($searchTerm, $options);
            }

            return $this->searchAllModels($searchTerm, $options, $currentPage);
        } catch (\Exception $e) {
            \Log::error('Search error: ' . $e->getMessage());
            return new LengthAwarePaginator([], 0, $this->perPage, $currentPage);
        }
    }

    private function sanitizeOptions(array $options): array
    {
        return [
            'category' => $options['category'] ?? 'all',
            'post_type' => $options['post_type'] ?? 'all',
            'from_date' => $options['from_date'] ?? null,
            'to_date' => $options['to_date'] ?? null,
            'order_type' => in_array($options['order_type'] ?? 'desc', ['asc', 'desc']) ? $options['order_type'] : 'desc',
        ];
    }

    private function applyDateFilters($query, array $options)
    {
        if (!empty($options['from_date'])) {
            $query->where('created_at', '>=', $options['from_date']);
        }
        if (!empty($options['to_date'])) {
            $query->where('created_at', '<=', $options['to_date']);
        }
        return $query;
    }

    private function searchByCategory(string $searchTerm, array $options): LengthAwarePaginator
    {
        $category = Category::find($options['category']);
        if (!$category) {
            return new LengthAwarePaginator([], 0, $this->perPage, 1);
        }

        $query = $category->news();
        if (!empty($searchTerm)) {
            $query = $query->search($searchTerm, function ($meiliSearch) {
                return $meiliSearch->setSettings([
                    'rankingRules' => [
                        'words',
                        'typo',
                        'proximity',
                        'attribute',
                        'sort',
                        'exactness',
                        'created_at:desc'
                    ]
                ]);
            });
        }

        $query = $this->applyDateFilters($query, $options);
        return $query->orderBy('created_at', $options['order_type'])
            ->paginate($this->perPage);
    }

    private function searchByPostType(string $searchTerm, array $options): LengthAwarePaginator
    {
        $model = $this->modelMap[$options['post_type']];
        $query = $model::search($searchTerm, function ($meiliSearch) {
            return $meiliSearch->setSettings([
                'rankingRules' => [
                    'words',
                    'typo',
                    'proximity',
                    'attribute',
                    'sort',
                    'exactness',
                    'created_at:desc'
                ]
            ]);
        });

        $query = $this->applyDateFilters($query, $options);
        return $query->orderBy('created_at', $options['order_type'])
            ->paginate($this->perPage);
    }

    private function searchAllModels(string $searchTerm, array $options, int $currentPage): LengthAwarePaginator
    {
        $results = collect();
        $limit = 100;

        foreach ($this->modelMap as $type => $model) {
            $query = $model::search($searchTerm, function ($meiliSearch) {
                return $meiliSearch->setSettings([
                    'rankingRules' => [
                        'words',
                        'typo',
                        'proximity',
                        'attribute',
                        'sort',
                        'exactness',
                        'created_at:desc'
                    ]
                ]);
            });

            $query = $this->applyDateFilters($query, $options);
            $items = $query->take($limit)
                ->get()
                ->map(fn($item) => $item->setAttribute('unique_id', "{$type}_{$item->id}"));
            $results = $results->merge($items);
        }

        $sortedResults = $results->sortByDesc('created_at')->values();
        $paginatedResults = $sortedResults->slice(($currentPage - 1) * $this->perPage, $this->perPage)->values();

        return new LengthAwarePaginator(
            $paginatedResults,
            $sortedResults->count(),
            $this->perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }
}


