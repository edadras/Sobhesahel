<?php

namespace App\Http\Resources\Api;

use App\Support\ApiContent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A public (verified) comment, with nested verified replies. Keys mirror
 * CommentItem.fromJson in mobile/lib/data/models/news_models.dart.
 */
class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $model = $this->resource;

        $replies = [];

        try {
            $relation = $model->replies ?? null;

            if ($relation !== null) {
                $verified = collect($relation)->filter(
                    fn ($reply) => ($reply->status ?? null) === 'verified'
                );

                $replies = CommentResource::collection($verified->values())->toArray($request);
            }
        } catch (\Throwable $e) {
            $replies = [];
        }

        return [
            'id' => (int) ($model->id ?? 0),
            'name' => trim((string) ($model->name ?? '')) ?: 'کاربر مهمان',
            'body' => (string) ($model->comment ?? ''),
            'created_at_jalali' => ApiContent::jalaliLong($model->created_at ?? null),
            'replies' => $replies,
        ];
    }
}
