<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\AuthorResource;
use App\Http\Resources\Api\NewsCardResource;
use App\Models\Author;
use App\Models\News;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * GET /authors/{user_type}/{id} — an author/journalist profile with a page of
 * their published news. user_type is "author" (Author model) or "user"
 * (journalist User model), matching the website author route.
 */
class AuthorController extends ApiController
{
    public function show(Request $request, string $userType, int $id)
    {
        if ($userType === 'author') {
            $subject = Author::findOrFail($id);
            $column = 'author_id';
        } elseif ($userType === 'user') {
            $subject = User::findOrFail($id);
            $column = 'user_id';
        } else {
            abort(404, 'نوع نویسنده نامعتبر است.');
        }

        $paginator = News::query()
            ->where($column, $id)
            ->where('lang_id', 1)
            ->where('is_published', true)
            ->orderBy('id', 'DESC')
            ->paginate($this->perPage($request))
            ->withQueryString();

        $posts = NewsCardResource::collection($paginator->getCollection())->toArray($request);

        return response()->json([
            'data' => [
                'author' => (new AuthorResource($subject))->toArray($request),
                'posts' => $posts,
            ],
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}
