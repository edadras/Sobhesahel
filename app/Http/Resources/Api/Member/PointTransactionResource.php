<?php

namespace App\Http\Resources\Api\Member;

use App\Http\Controllers\Api\Member\Concerns\MemberApiResponses;
use App\Models\PointTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * تراکنش امتیاز مطابق PointTransaction.fromJson اپ:
 * {id, points, balance_after, rule_code, description, created_at, created_at_jalali}
 *
 * @property PointTransaction $resource
 */
class PointTransactionResource extends JsonResource
{
    use MemberApiResponses;

    public function toArray(Request $request): array
    {
        /** @var PointTransaction $t */
        $t = $this->resource;

        return [
            'id' => (int) $t->id,
            'points' => (int) $t->points,
            'balance_after' => (int) $t->balance_after,
            'rule_code' => (string) ($t->rule_code ?? ''),
            'description' => (string) ($t->description ?? ''),
            'created_at' => $t->created_at?->toIso8601String() ?? '',
            'created_at_jalali' => $this->jalali($t->created_at, '%d %B %Y - %H:%M') ?? '',
        ];
    }
}
