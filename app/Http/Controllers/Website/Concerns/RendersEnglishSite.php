<?php

namespace App\Http\Controllers\Website\Concerns;

use App\Http\Resources\ContentMetaDataResource;
use App\Models\Language;
use App\Models\News;
use Carbon\Carbon;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Shared helpers for the English (LTR) site controllers (WP-15 — چندزبانه).
 *
 * The English site is best-effort: every page is rendered eagerly inside
 * ltrView() so that any failure (missing data shape, legacy blade issue,
 * missing English content) falls back to a clean "coming soon" page instead
 * of a 500 error.
 */
trait RendersEnglishSite
{
    /**
     * lang_id value used by English rows in the content tables.
     */
    protected function englishLangId(): int
    {
        return Language::contentLangId('en');
    }

    /**
     * Render an LTR blade eagerly; on any render failure return the clean
     * English "coming soon" page instead of a 500. HTTP aborts (404, ...)
     * raised while rendering are passed through untouched.
     */
    protected function ltrView(string $view, array $data = [], ?string $title = null)
    {
        try {
            return response(view($view, $data)->render());
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return $this->comingSoon($title);
        }
    }

    /**
     * Clean English placeholder page (never throws).
     */
    protected function comingSoon(?string $title = null)
    {
        return response()->view('website.ltr.coming-soon', [
            'page_title' => $title,
        ]);
    }

    /**
     * Ensure the array shape the legacy LTR blades expect on every item
     * coming out of ContentMetaDataResource (posted_at, author sub-array).
     */
    protected function normalizeLtrItem(array $item): array
    {
        if (!isset($item['posted_at'])) {
            try {
                $item['posted_at'] = !empty($item['publish_at'])
                    ? Carbon::parse($item['publish_at'])->format('d M Y')
                    : '';
            } catch (\Throwable $e) {
                $item['posted_at'] = '';
            }
        }

        if (isset($item['author']) && is_array($item['author'])) {
            $item['author'] += [
                'avatar' => '/asset/img/user05.png',
                'first_name' => '',
                'last_name' => '',
                'nik_name' => '',
            ];
        }

        return $item;
    }

    /**
     * @param iterable<array> $items resolved ContentMetaDataResource arrays
     */
    protected function normalizeLtrItems(iterable $items): array
    {
        $normalized = [];

        foreach ($items as $key => $item) {
            $normalized[$key] = is_array($item) ? $this->normalizeLtrItem($item) : $item;
        }

        return array_values($normalized);
    }

    /**
     * Latest published English news as LTR-ready arrays.
     */
    protected function enLatestNews(int $limit = 5): array
    {
        $items = News::where('lang_id', $this->englishLangId())
            ->where('is_published', true)
            ->orderBy('id', 'DESC')
            ->take($limit)
            ->get();

        return $this->normalizeLtrItems(
            ContentMetaDataResource::collection($items)->resolve()
        );
    }

    /**
     * Most viewed published English news as LTR-ready arrays.
     */
    protected function enMostViewedNews(int $limit = 3): array
    {
        $items = News::where('lang_id', $this->englishLangId())
            ->where('is_published', true)
            ->orderBy('visits', 'DESC')
            ->take($limit)
            ->get();

        return $this->normalizeLtrItems(
            ContentMetaDataResource::collection($items)->resolve()
        );
    }

    /**
     * English brand name used in <title> tags.
     */
    protected function enBrandName(): string
    {
        try {
            return setting('general.en_brand_name') ?? 'Sobhe Sahel Media Group';
        } catch (\Throwable $e) {
            return 'Sobhe Sahel Media Group';
        }
    }
}
