<?php

namespace App\Http\Resources\Api\Member;

use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Schema;

/**
 * پلن اشتراک مطابق SubscriptionPlan.fromJson اپ:
 * {id, name, duration_days, price, points_price?, features:[...], badge?}
 *
 * points_price و badge فقط اگر ستون‌هایشان روی جدول موجود باشد ارسال می‌شوند
 * (Schema::hasColumn) — در غیر این‌صورت null.
 *
 * @property SubscriptionPlan $resource
 */
class SubscriptionPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var SubscriptionPlan $p */
        $p = $this->resource;

        $features = [];

        try {
            $raw = $p->features;
            if (is_array($raw)) {
                $features = array_values(array_map('strval', $raw));
            }
        } catch (\Throwable) {
            $features = [];
        }

        return [
            'id' => (int) $p->id,
            'name' => (string) ($p->name ?? ''),
            'duration_days' => (int) ($p->duration_days ?? 0),
            'price' => (int) ($p->price ?? 0),
            'points_price' => $this->optionalColumn($p, 'points_price'),
            'features' => $features,
            'badge' => $this->badge($p),
        ];
    }

    protected function optionalColumn(SubscriptionPlan $p, string $column): ?int
    {
        try {
            if (Schema::hasColumn($p->getTable(), $column) && $p->{$column} !== null) {
                return (int) $p->{$column};
            }
        } catch (\Throwable) {
            // ignore
        }

        return null;
    }

    protected function badge(SubscriptionPlan $p): ?string
    {
        try {
            if (Schema::hasColumn($p->getTable(), 'badge') && filled($p->badge)) {
                return (string) $p->badge;
            }
        } catch (\Throwable) {
            // ignore
        }

        return null;
    }
}
