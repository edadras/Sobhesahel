<?php

namespace App\Http\Resources\Api;

use App\Http\Resources\Api\Concerns\PresentsContent;
use App\Support\ApiContent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * List "card" for any content type (news/note/video/podcast/photo).
 * Keys mirror NewsCard.fromJson in mobile/lib/data/models/news_models.dart.
 */
class NewsCardResource extends JsonResource
{
    use PresentsContent;

    public function toArray(Request $request): array
    {
        $model = $this->resource;

        return [
            'id' => (int) $model->id,
            'code' => (string) $model->id,
            'type' => ApiContent::typeOf($model),
            'title' => (string) ($model->title ?? ''),
            'lead' => trim(strip_tags((string) ($model->short_description ?? ''))),
            'image_url' => ApiContent::imageUrl($model),
            'category' => $this->cardCategory($model),
            'author' => $this->authorName($model),
            'published_at' => ApiContent::iso($model->publish_at ?? null),
            'published_at_jalali' => ApiContent::jalali($model->publish_at ?? null),
            'url' => $this->safeUrl($model),
            'visits' => (int) ($model->visits ?? 0),
            'bookmarked' => false,
            'duration' => null,
        ];
    }
}
