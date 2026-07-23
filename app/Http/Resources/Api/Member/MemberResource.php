<?php

namespace App\Http\Resources\Api\Member;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * سریال‌سازی عضو دقیقاً مطابق Member.fromJson اپ فلاتر:
 * {id, first_name, last_name, full_name, mobile, email, avatar_url, city,
 *  birth_date, bio, locale, is_active, created_at}
 *
 * @property Member $resource
 */
class MemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Member $m */
        $m = $this->resource;

        return [
            'id' => (int) $m->id,
            'first_name' => (string) ($m->first_name ?? ''),
            'last_name' => (string) ($m->last_name ?? ''),
            'full_name' => $m->fullName(),
            'mobile' => (string) ($m->mobile ?? ''),
            'email' => $m->email !== null ? (string) $m->email : null,
            'avatar_url' => $m->avatarUrl(),
            'city' => $m->city !== null ? (string) $m->city : null,
            'birth_date' => $this->birthDate($m),
            'bio' => $m->bio !== null ? (string) $m->bio : null,
            'locale' => (string) ($m->locale ?? 'fa'),
            'is_active' => (bool) ($m->is_active ?? true),
            'created_at' => $m->created_at?->toIso8601String(),
        ];
    }

    protected function birthDate(Member $m): ?string
    {
        try {
            return $m->birth_date?->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
