<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Question extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_PUBLISHED = 'published';
    const STATUS_REJECTED = 'rejected';

    protected $guarded = ['id'];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_admin_created' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(QuestionCategory::class, 'category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class)->orderBy('sort')->orderBy('id');
    }

    public function published_answers()
    {
        return $this->hasMany(Answer::class)
            ->where('is_published', true)
            ->orderBy('sort')
            ->orderBy('id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function getSlugAttribute(): string
    {
        $slug = preg_replace('/[^a-zA-Z0-9آ-ی۰-۹\-]/u', '-', (string) $this->title);

        return trim($slug, '-') ?: 'question';
    }

    public function getUrl(): string
    {
        return route('website.rtl.qa.single', ['id' => $this->id, 'slug' => $this->slug]);
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        if (empty($this->attachment)) {
            return null;
        }

        return Storage::disk('public')->url($this->attachment);
    }

    public function hasExpertAnswer(): bool
    {
        return $this->answers()
            ->where('is_expert', true)
            ->where('is_published', true)
            ->exists();
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'در انتظار بررسی',
            self::STATUS_PUBLISHED => 'منتشر شده',
            self::STATUS_REJECTED => 'رد شده',
        ];
    }
}
