<?php

namespace App\Models;

use App\Traits\ContentTrait;
use App\Traits\InteractsWithSiteSearch;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Spatie\Tags\HasTags;

class Note extends Model
{
    use HasTags, ContentTrait, Searchable, InteractsWithSiteSearch;

    protected $guarded = ['id'];

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'short_description' => $this->searchPlainText($this->short_description, 1000),
            'seo_title' => $this->seo_title,
            'body' => $this->searchPlainText($this->body),
            'tags' => $this->searchTags(),
            'author' => $this->searchAuthorName(),
            'author_id' => $this->author_id,
            'publish_at' => $this->searchPublishAt(),
            'created_at' => optional($this->created_at)->format('Y-m-d H:i:s'),
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->status === 'published' && (bool) $this->is_published;
    }
}
