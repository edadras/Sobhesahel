<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A market price row (currency/gold/coin/...). Keys mirror PriceItem.fromJson
 * in mobile/lib/data/models/news_models.dart.
 */
class PriceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $model = $this->resource;

        $value = null;

        try {
            $value = $model->formatted_price;
        } catch (\Throwable $e) {
            $value = null;
        }

        if ($value === null || $value === '') {
            $value = (string) ($model->price ?? '');
        }

        return [
            'title' => (string) ($model->name ?? ''),
            'value' => (string) $value,
            'change' => (float) ($model->change_percent ?? 0),
            'unit' => $this->nullable($model->unit ?? null),
        ];
    }

    private function nullable($value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }
}
