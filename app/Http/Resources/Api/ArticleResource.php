<?php

namespace App\Http\Resources\Api;

use App\Http\Resources\Api\Concerns\PresentsContent;
use App\Models\Comment;
use App\Models\News;
use App\Support\ApiContent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Full article/detail for a single content item. Keys mirror
 * ArticleDetail.fromJson in mobile/lib/data/models/news_models.dart.
 */
class ArticleResource extends JsonResource
{
    use PresentsContent;

    public function toArray(Request $request): array
    {
        $model = $this->resource;
        $type = ApiContent::typeOf($model);

        return [
            'id' => (int) $model->id,
            'code' => (string) $model->id,
            'type' => $type,
            'rotitr' => $this->nullable($model->sub_title ?? null),
            'title' => (string) ($model->title ?? ''),
            'lead' => trim(strip_tags((string) ($model->short_description ?? ''))),
            'body_html' => (string) ($model->body ?? ''),
            'subtitles' => $this->subtitles($model),
            'image_url' => ApiContent::imageUrl($model),
            'images' => $this->images($model),
            'media_url' => $this->mediaUrl($model, $type),
            'category' => $this->cardCategory($model),
            'tags' => $this->tagList($model),
            'author' => $this->author($model),
            'published_at' => ApiContent::iso($model->publish_at ?? null),
            'published_at_jalali' => ApiContent::jalali($model->publish_at ?? null),
            'visits' => (int) ($model->visits ?? 0),
            'related' => $this->related($model, $type),
            'gallery' => $this->gallery($model, $type),
            'video_embed' => $type === 'video' ? $this->nullable($model->embed ?? null) : null,
            'audio_url' => $type === 'podcast' ? $this->firstAttachmentUrl($model) : null,
            'show_comments' => (bool) ($model->show_comments ?? true),
            'comments_count' => $this->commentsCount($model, $type),
            'bookmarked' => false,
            'seo' => $this->seo($model),
        ];
    }

    private function nullable($value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    /**
     * Author as an embedded AuthorCard (Author record, else authoring User).
     */
    private function author($model): ?array
    {
        try {
            $author = ($model->author_id ?? null) ? $model->author : $model->user;

            if ($author === null) {
                return null;
            }

            return (new AuthorResource($author))->toArray(request());
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Normalize the subtitles JSON to a flat list of strings.
     */
    private function subtitles($model): array
    {
        try {
            $raw = $model->subtitles ?? null;

            if (! is_array($raw)) {
                return [];
            }

            $out = [];

            foreach ($raw as $item) {
                if (is_string($item) || is_numeric($item)) {
                    $text = trim((string) $item);
                } elseif (is_array($item)) {
                    $text = trim((string) ($item['title'] ?? $item['text'] ?? $item['value'] ?? reset($item) ?? ''));
                } else {
                    $text = '';
                }

                if ($text !== '') {
                    $out[] = $text;
                }
            }

            return $out;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Complementary images (second/third), absolute and filtered.
     */
    private function images($model): array
    {
        $out = [];

        foreach (['image_second', 'image_third'] as $field) {
            try {
                if (! empty($model->{$field}) && method_exists($model, 'getFileUrl')) {
                    $url = ApiContent::abs($model->getFileUrl($model->{$field}));
                    if ($url !== null) {
                        $out[] = $url;
                    }
                }
            } catch (\Throwable $e) {
                // skip
            }
        }

        return $out;
    }

    /**
     * Main media file URL (news audio/video attachment or podcast audio).
     */
    private function mediaUrl($model, string $type): ?string
    {
        try {
            if (method_exists($model, 'getMediaFileUrl')) {
                $url = $model->getMediaFileUrl();

                if (! empty($url)) {
                    return ApiContent::abs($url);
                }
            }
        } catch (\Throwable $e) {
            // fall through
        }

        if ($type === 'podcast') {
            return $this->firstAttachmentUrl($model);
        }

        return null;
    }

    /**
     * Gallery images for `photo` items, resolved to absolute URLs.
     */
    private function gallery($model, string $type): array
    {
        if ($type !== 'photo') {
            return [];
        }

        return $this->attachmentUrls($model);
    }

    /**
     * Resolve the `attachments` JSON to a flat list of absolute URLs.
     */
    private function attachmentUrls($model): array
    {
        try {
            $raw = $model->attachments ?? null;

            if (! is_array($raw)) {
                return [];
            }

            $out = [];

            foreach ($raw as $item) {
                $path = $this->attachmentPath($item);

                if ($path === null) {
                    continue;
                }

                $url = $this->resolveStoragePath($path);

                if ($url !== null) {
                    $out[] = $url;
                }
            }

            return $out;
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function firstAttachmentUrl($model): ?string
    {
        $urls = $this->attachmentUrls($model);

        return $urls[0] ?? null;
    }

    private function attachmentPath($item): ?string
    {
        if (is_string($item) && trim($item) !== '') {
            return $item;
        }

        if (is_array($item)) {
            foreach (['url', 'path', 'file', 'src', 'image'] as $key) {
                if (! empty($item[$key]) && is_string($item[$key])) {
                    return $item[$key];
                }
            }
        }

        return null;
    }

    private function resolveStoragePath(string $path): ?string
    {
        if (preg_match('#^(https?:)?//#i', $path)) {
            return $path;
        }

        try {
            if (Storage::disk('public')->exists($path)) {
                return ApiContent::abs(Storage::disk('public')->url($path));
            }

            if (Storage::disk('media')->exists($path)) {
                return ApiContent::abs(Storage::disk('media')->url($path));
            }
        } catch (\Throwable $e) {
            // fall through to a best-effort absolute URL
        }

        return ApiContent::abs($path);
    }

    /**
     * Related items: smart-related for news, latest siblings otherwise.
     */
    private function related($model, string $type): array
    {
        try {
            if ($model instanceof News && method_exists($model, 'relatedSmart')) {
                $items = $model->relatedSmart(5);
            } else {
                $items = $model::query()
                    ->where('id', '!=', $model->id)
                    ->where('is_published', true)
                    ->orderBy('id', 'DESC')
                    ->take(5)
                    ->get();
            }

            return NewsCardResource::collection($items)->toArray(request());
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function commentsCount($model, string $type): int
    {
        try {
            if (! Schema::hasTable('comments')) {
                return 0;
            }

            $column = $type === 'note' ? 'note_id' : ($type === 'news' ? 'news_id' : null);

            if ($column === null || ! Schema::hasColumn('comments', $column)) {
                return 0;
            }

            return (int) Comment::where($column, $model->id)
                ->where('status', 'verified')
                ->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function seo($model): array
    {
        try {
            if (method_exists($model, 'getSeoData')) {
                $seo = $model->getSeoData();

                return [
                    'title' => (string) ($seo['title'] ?? $model->title ?? ''),
                    'description' => trim(strip_tags((string) ($seo['description'] ?? ''))),
                    'image' => ApiContent::abs($seo['image'] ?? null),
                ];
            }
        } catch (\Throwable $e) {
            // fall through
        }

        return [
            'title' => (string) ($model->title ?? ''),
            'description' => trim(strip_tags((string) ($model->short_description ?? ''))),
            'image' => ApiContent::imageUrl($model),
        ];
    }
}
