<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $guarded = ['id'];
public $timestamps = false;


    // Comment belongs to a News post (if applicable)
    public function news()
    {
        return $this->belongsTo(News::class);
    }

    // Comment belongs to a Note (if applicable)
    public function note()
    {
        return $this->belongsTo(Note::class);
    }

    // Comment may belong to a user (optional)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // A comment may be a reply to another comment
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'reply_to');
    }

    // A comment may have replies
    public function replies()
    {
        return $this->hasMany(Comment::class, 'reply_to');
    }
}
