<?php

namespace App\Traits;

trait InteractsWithSiteSearch
{
    protected function searchPlainText(?string $value, int $maxLength = 50000): string
    {
        $text = html_entity_decode(strip_tags($value ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? '';

        return mb_substr(trim($text), 0, $maxLength);
    }

    protected function searchTags(): array
    {
        if (! method_exists($this, 'tags')) {
            return [];
        }

        return $this->tags->pluck('name')->filter()->values()->all();
    }

    protected function searchAuthorName(): ?string
    {
        return optional($this->author)->name;
    }

    protected function searchCategories(): array
    {
        if (! method_exists($this, 'categories')) {
            return [];
        }

        return $this->categories->pluck('title')->filter()->values()->all();
    }

    protected function searchCategoryIds(): array
    {
        if (! method_exists($this, 'categories')) {
            return [];
        }

        return $this->categories->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
    }

    protected function searchPublishAt(): ?string
    {
        return optional($this->publish_at)->format('Y-m-d H:i:s');
    }
}
