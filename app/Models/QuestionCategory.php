<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionCategory extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            if (empty($model->slug)) {
                $model->slug = preg_replace('/[^a-zA-Z0-9آ-ی۰-۹\-]/u', '-', $model->title);
            } else {
                $model->slug = preg_replace('/[^a-zA-Z0-9آ-ی۰-۹\-]/u', '-', $model->slug);
            }
        });
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
