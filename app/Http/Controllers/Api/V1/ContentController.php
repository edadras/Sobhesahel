<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\ArticleResource;
use App\Http\Resources\Api\NewsCardResource;
use App\Models\Category;
use App\Models\News;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * GET /content/{type}            — paginated list of a content type.
 * GET /content/{type}/{code}     — a single item (code = model id).
 */
class ContentController extends ApiController
{
    public function index(Request $request, string $type)
    {
        $modelClass = $this->modelClassOrFail($type);

        $query = $this->applySort($this->publicQuery($modelClass), $request);

        // ?category= (slug or id) — News only carries category relations.
        $category = $request->query('category');

        if ($modelClass === News::class && filled($category)) {
            $this->applyCategoryFilter($query, (string) $category);
        }

        // ?tag= (Spatie tag name).
        $tag = $request->query('tag');

        if (filled($tag)) {
            $query->whereHas('tags', fn (Builder $q) => $q->where('name', (string) $tag));
        }

        $paginator = $query->paginate($this->perPage($request))->withQueryString();

        return NewsCardResource::collection($paginator);
    }

    public function show(string $type, string $code)
    {
        $modelClass = $this->modelClassOrFail($type);

        $model = $modelClass::query()->find($code);

        if ($model === null || ! $model->is_published) {
            abort(404, 'محتوا یافت نشد.');
        }

        return new ArticleResource($model);
    }

    private function applyCategoryFilter(Builder $query, string $category): void
    {
        $categoryModel = ctype_digit($category)
            ? Category::find($category)
            : Category::where('slug', $category)->first();

        if ($categoryModel === null) {
            return;
        }

        $ids = $categoryModel->children->pluck('id')->prepend($categoryModel->id);

        $query->whereHas('categories', fn (Builder $q) => $q->whereIn('categories.id', $ids));
    }
}
