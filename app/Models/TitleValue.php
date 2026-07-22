<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TitleValue extends Model
{
    protected $fillable = ['title', 'value', 'data'];

    protected $casts = [
        'data_fields' => 'array',
    ];

    public function titleable()
    {
        return $this->morphTo();
    }
}
