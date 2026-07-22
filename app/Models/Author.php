<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    protected $guarded = ['id'];

    public function news()
    {
        return $this->hasMany(News::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function videos()
    {
        return $this->hasMany(Video::class);
    }

    public function galleries()
    {
        return $this->hasMany(Gallery::class);
    }
}
