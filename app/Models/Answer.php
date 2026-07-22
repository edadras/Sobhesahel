<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_expert' => 'boolean',
        'is_published' => 'boolean',
        'sort' => 'integer',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
