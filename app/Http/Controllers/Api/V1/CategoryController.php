<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\NewsCardResource;
use App\Models\Category;
use App\Models\News;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * GET /category/{slug} — news of a service (category + its children).
 * GET /tag/{name}      — content tagged with a Spatie tag.
 */
class CategoryController extends ApiController
{
    public function index(Request $request, string $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $ids = $category->children->pluck('id')->prepend($category->id);

        $query = $this->publicQuery(News::class)
            ->orderBy('id', 'DESC')
            ->whereHas('categories', fn (Builder $q) => $q->whereIn('categories.id', $ids));

        $paginator = $query->paginate($this->perPage($request))->withQueryString();

        return NewsCardResource::collection($paginator);
    }

    public function tag(Request $request, string $name)
    {
        $query = $this->publicQuery(News::class)
            ->orderBy('id', 'DESC')
            ->whereHas('tags', fn (Builder $q) => $q->where('name', $name));

        $paginator = $query->paginate($this->perPage($request))->withQueryString();

        return NewsCardResource::collection($paginator);
    }
}
