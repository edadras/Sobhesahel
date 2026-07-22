<?php

namespace App\Models;

use App\Traits\ContentTrait;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Tags\HasTags;

class Podcast extends Model
{
    use HasTags,ContentTrait;

    protected $guarded = ['id'];

    protected $casts = [
        'attachments' => 'array'
    ];

}
