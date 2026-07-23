<?php

namespace App\Http\Resources\Api;

use App\Support\ApiContent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * An active live stream. Keys mirror LiveStream.fromJson in
 * mobile/lib/data/models/news_models.dart.
 */
class LiveStreamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $model = $this->resource;

        $url = null;

        try {
            $url = $model->embed_src;
        } catch (\Throwable $e) {
            $url = null;
        }

        if (empty($url)) {
            $url = (string) ($model->embed_url ?? '');
        }

        return [
            'id' => (int) ($model->id ?? 0),
            'title' => (string) ($model->title ?? ''),
            'url' => (string) $url,
            'thumbnail_url' => ApiContent::abs($model->image ?? null),
            'is_live' => (bool) ($model->is_live ?? false),
        ];
    }
}
