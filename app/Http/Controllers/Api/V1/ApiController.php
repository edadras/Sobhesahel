<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Shared base for the public News API (v1) controllers. Provides the standard
 * "published, Persian (lang_id = 1)" query used by every public list and the
 * sort helper. Public lists never leak drafts, pending, suspended or
 * soft-deleted items: is_published is only ever true for status = published
 * (see App\Traits\ContentTrait::updatePublicationStatus) and SoftDeletes
 * excludes trashed rows automatically.
 */
abstract class ApiController extends Controller
{
    /**
     * Public list of items in Persian, published only, newest first by default.
     *
     * @param  class-string  $modelClass
     * @return Builder
     */
    protected function publicQuery(string $modelClass): Builder
    {
        return $modelClass::query()
            ->where('lang_id', 1)
            ->where('is_published', true);
    }

    /**
     * Apply ?sort=newest|oldest (by id, matching the website ordering).
     */
    protected function applySort(Builder $query, Request $request): Builder
    {
        $sort = strtolower((string) $request->query('sort', 'newest'));

        return $query->orderBy('id', $sort === 'oldest' ? 'ASC' : 'DESC');
    }

    /**
     * Resolve a content-type string to its model class or send a 404.
     *
     * @return class-string
     */
    protected function modelClassOrFail(string $type): string
    {
        $modelClass = ApiContent::modelClass($type);

        if ($modelClass === null) {
            abort(404, 'نوع محتوا نامعتبر است.');
        }

        return $modelClass;
    }

    /**
     * Per-page size, clamped to a sane range.
     */
    protected function perPage(Request $request, int $default = 20, int $max = 50): int
    {
        $perPage = (int) $request->query('per_page', $default);

        return max(1, min($perPage, $max));
    }
}
