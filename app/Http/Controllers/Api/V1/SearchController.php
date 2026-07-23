<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\NewsCardResource;
use App\Services\SiteSearchService;
use App\Support\ApiContent;
use Illuminate\Http\Request;

/**
 * GET /search — advanced search over posts + authors, reusing the site's
 * SiteSearchService (Meilisearch with a database fallback).
 *
 * Response: { data: { posts: [card], authors: [author_card] }, meta }.
 * posts is the first page of results (the app's SearchResults DTO reads a flat
 * list); pagination metadata is surfaced under meta for completeness.
 */
class SearchController extends ApiController
{
    public function index(Request $request, SiteSearchService $search)
    {
        $term = trim((string) $request->query('q', ''));
        $perPage = $this->perPage($request);

        $posts = [];
        $meta = ['current_page' => 1, 'last_page' => 1, 'per_page' => $perPage, 'total' => 0];

        try {
            $options = $this->buildOptions($request);

            $paginator = $search->paginate($term, $options, 1, $perPage);

            $posts = NewsCardResource::collection($paginator->getCollection())->toArray($request);

            $meta = [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ];
        } catch (\Throwable $e) {
            report($e);
        }

        $authors = [];

        try {
            $authors = $search->searchAuthors($term)
                ->map(fn ($a) => [
                    'id' => (int) ($a['id'] ?? 0),
                    'user_type' => (string) ($a['type'] ?? 'author'),
                    'name' => (string) ($a['name'] ?? ''),
                    'avatar_url' => ApiContent::abs($a['avatar'] ?? null),
                    'bio' => null,
                    'url' => (string) ($a['url'] ?? ''),
                    'posts_count' => 0,
                ])
                ->values()
                ->all();
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'data' => [
                'posts' => $posts,
                'authors' => $authors,
            ],
            'meta' => $meta,
        ]);
    }

    /**
     * Map the public query params to SiteSearchService options.
     */
    private function buildOptions(Request $request): array
    {
        $sort = strtolower((string) $request->query('sort', 'newest'));

        $options = [
            'post_type' => (string) $request->query('type', 'all'),
            'category' => (string) $request->query('category', 'all'),
            'author' => (string) $request->query('author', 'all'),
            'order_type' => $sort === 'oldest' ? 'ASC' : 'DESC',
        ];

        // Dates are Jalali (Y/m/d) for SiteSearchService; only forward
        // well-formed values so a bad param can never blow up the query.
        foreach (['from' => 'from_date', 'to' => 'to_date'] as $param => $key) {
            $value = (string) $request->query($param, '');

            if ($value !== '' && preg_match('#^\d{4}/\d{1,2}/\d{1,2}$#', $value)) {
                $options[$key] = $value;
            }
        }

        return $options;
    }
}
