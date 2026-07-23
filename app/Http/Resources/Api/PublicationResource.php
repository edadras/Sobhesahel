<?php

namespace App\Http\Resources\Api;

use App\Support\ApiContent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * Newspaper archive issue. Keys mirror Publication.fromJson in
 * mobile/lib/data/models/news_models.dart.
 */
class PublicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $model = $this->resource;

        $date = $model->archive_date ?? $model->publish_at ?? null;

        return [
            'id' => (int) ($model->id ?? 0),
            'title' => (string) ($model->title ?? ''),
            'cover_url' => ApiContent::imageUrl($model),
            'date_jalali' => ApiContent::jalaliLong($date),
            'type' => $this->typeLabel($model),
            'pdf_url' => $this->pdfUrl($model),
            'number' => $this->nullable($model->archive_number ?? null),
        ];
    }

    private function typeLabel($model): string
    {
        try {
            return (string) ($model->type_label ?? 'روزنامه');
        } catch (\Throwable $e) {
            return 'روزنامه';
        }
    }

    private function pdfUrl($model): ?string
    {
        try {
            if (empty($model->archive_file)) {
                return null;
            }

            return ApiContent::abs(Storage::url($model->archive_file));
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function nullable($value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }
}
