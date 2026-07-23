<?php

namespace App\Http\Resources\Api;

use App\Models\Author;
use App\Models\News;
use App\Models\User;
use App\Support\ApiContent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * Author "card"/profile. Accepts an Author or a User model. Keys mirror
 * AuthorCard.fromJson in mobile/lib/data/models/news_models.dart.
 */
class AuthorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $model = $this->resource;
        $isUser = $model instanceof User;

        return [
            'id' => (int) ($model->id ?? 0),
            'user_type' => $isUser ? 'user' : 'author',
            'name' => trim((string) ($model->name ?? '')),
            'avatar_url' => $this->avatar($model, $isUser),
            'bio' => $this->bio($model),
            'url' => $this->authorUrl($model, $isUser),
            'posts_count' => $this->postsCount($model, $isUser),
        ];
    }

    private function avatar($model, bool $isUser): ?string
    {
        try {
            $raw = $isUser ? ($model->avatar_url ?? null) : ($model->avatar ?? null);

            if (empty($raw)) {
                return null;
            }

            if (preg_match('#^(https?:)?//#i', $raw)) {
                return $raw;
            }

            return ApiContent::abs(Storage::url($raw));
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function bio($model): ?string
    {
        $bio = trim((string) ($model->bio ?? ''));

        return $bio === '' ? null : $bio;
    }

    private function authorUrl($model, bool $isUser): string
    {
        try {
            return (string) route('website.rtl.author', [
                'user_type' => $isUser ? 'user' : 'author',
                'id' => $model->id,
            ]);
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function postsCount($model, bool $isUser): int
    {
        try {
            $column = $isUser ? 'user_id' : 'author_id';

            return (int) News::where($column, $model->id)
                ->where('is_published', true)
                ->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
