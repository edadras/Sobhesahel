<?php

namespace App\Support;

class SearchIndexSettings
{
    public static function meilisearch(): array
    {
        return [
            'searchableAttributes' => [
                'title',
                'sub_title',
                'short_description',
                'seo_title',
                'tags',
                'author',
                'categories',
                'body',
            ],
            'filterableAttributes' => [
                'publish_at',
                'author_id',
                'category_ids',
            ],
            'sortableAttributes' => [
                'publish_at',
            ],
            'rankingRules' => [
                'words',
                'typo',
                'proximity',
                'attribute',
                'sort',
                'exactness',
            ],
            'pagination' => [
                'maxTotalHits' => 50000,
            ],
        ];
    }

    /**
     * @return array<string, class-string<\Illuminate\Database\Eloquent\Model>>
     */
    public static function modelsByIndex(): array
    {
        return [
            'news' => \App\Models\News::class,
            'galleries' => \App\Models\Gallery::class,
            'videos' => \App\Models\Video::class,
            'notes' => \App\Models\Note::class,
            'podcasts' => \App\Models\Podcast::class,
        ];
    }

    public static function forScoutConfig(): array
    {
        $settings = self::meilisearch();

        return array_fill_keys(array_keys(self::modelsByIndex()), $settings);
    }
}
