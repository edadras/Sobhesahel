<?php

namespace App\Http\Resources\Website;

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

        return $data;
    }
}
