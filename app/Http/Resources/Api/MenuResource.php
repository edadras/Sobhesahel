<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A navigation/service node (from the Category tree). Keys mirror
 * MenuItem.fromJson in mobile/lib/data/models/news_models.dart.
 */
class MenuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $model = $this->resource;

        $children = [];

        try {
            $relation = $model->childrenRecursive ?? null;

            if ($relation !== null && count($relation) > 0) {
                $children = MenuResource::collection($relation)->toArray($request);
            }
        } catch (\Throwable $e) {
            $children = [];
        }

        return [
            'id' => (int) ($model->id ?? 0),
            'title' => (string) ($model->title ?? ''),
            'slug' => (string) ($model->slug ?? ''),
            'type' => 'category',
            'children' => $children,
        ];
    }
}
