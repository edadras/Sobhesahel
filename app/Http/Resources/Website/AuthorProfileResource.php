<?php

namespace App\Http\Resources\Website;

use App\Models\Author;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AuthorProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);

        $data['user_type'] = ($this->resource instanceof User) ? 'user' : 'author';

        try {
            if ($this->resource instanceof User){
                $data['avatar'] = $data['avatar_url'] == null ? '/asset/img/user05.png' : Storage::url($data['avatar_url']);
            }else{
                $data['avatar'] = $data['avatar'] == null ? '/asset/img/user05.png' : Storage::url($data['avatar']);
            }
        }catch (\Exception $e){
            $data['avatar'] = '/asset/img/user05.png';
        }

        if (!isset($data['nik_name'])){
            $data['nik_name'] = '';
        }

        if (!isset($data['bio'])){
            $data['bio'] = '';
        }

        // Author type badge (Persian label); only meaningful for Author records.
        $data['type_label'] = ($this->resource instanceof Author)
            ? (Author::TYPES[$data['type'] ?? null] ?? null)
            : null;

        $data['resume'] = $data['resume'] ?? null;
        $data['work_history'] = is_array($data['work_history'] ?? null) ? array_values(array_filter($data['work_history'])) : [];
        $data['social_links'] = is_array($data['social_links'] ?? null) ? array_values(array_filter($data['social_links'])) : [];

        // Contact info is for admin panel display only — never expose it publicly.
        unset($data['email'], $data['phone']);

        return $data;
    }
}
