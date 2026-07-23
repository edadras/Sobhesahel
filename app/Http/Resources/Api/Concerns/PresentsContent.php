<?php

namespace App\Http\Resources\Api\Concerns;

use App\Models\News;
use App\Support\ApiContent;
use Illuminate\Database\Eloquent\Model;

/**
 * Null-safe presenters shared by the card and article API resources. Every
 * method degrades to a sensible empty value rather than throwing.
 */
trait PresentsContent
{
    /**
     * First category of a News item as {title, slug}, or null for other types.
     */
    protected function cardCategory(Model $model): ?array
    {
        try {
            if (! ($model instanceof News)) {
                return null;
            }

            $category = $model->categories()->first();

            if ($category === null) {
                return null;
            }

            return [
                'title' => (string) ($category->title ?? ''),
                'slug' => (string) ($category->slug ?? ''),
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Display name of the item's author (Author record, else authoring User).
     */
    protected function authorName(Model $model): ?string
    {
        try {
            $author = ($model->author_id ?? null) ? $model->author : $model->user;

            $name = trim((string) ($author->name ?? ''));

            return $name === '' ? null : $name;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * The public website URL of the item, or empty string on failure.
     */
    protected function safeUrl(Model $model): string
    {
        try {
            return (string) $model->getUrl();
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Spatie tag names as a flat list of strings.
     */
    protected function tagList(Model $model): array
    {
        try {
            return $model->tags
                ->map(fn ($tag) => (string) $tag->name)
                ->filter(fn ($name) => $name !== '')
                ->values()
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }
}
