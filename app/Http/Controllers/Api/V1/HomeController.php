<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\LiveStreamResource;
use App\Http\Resources\Api\NewsCardResource;
use App\Http\Resources\Api\PriceResource;
use App\Http\Resources\Api\PublicationResource;
use App\Models\Archive;
use App\Models\Category;
use App\Models\FeaturedNews;
use App\Models\Gallery;
use App\Models\LiveStream;
use App\Models\MarketPrice;
use App\Models\News;
use App\Models\Podcast;
use App\Models\Video;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * GET /home — the app home screen payload. Every section is independently
 * guarded so a missing table or a broken row degrades to an empty section
 * instead of a 500. Shape matches HomePayload.fromJson in the app.
 */
class HomeController extends ApiController
{
    public function index(Request $request)
    {
        return response()->json([
            'data' => [
                'breaking' => $this->cards(fn () => FeaturedNews::getNewsByBoxTitle('urgent')),
                'slider' => $this->cards(fn () => FeaturedNews::getNewsByBoxTitle('top_slider')),
                'latest' => $this->cards(fn () => $this->latest(News::class, 15)),
                'boxes' => $this->boxes(),
                'videos' => $this->cards(fn () => $this->latest(Video::class, 6)),
                'podcasts' => $this->cards(fn () => $this->latest(Podcast::class, 6)),
                'galleries' => $this->cards(fn () => $this->latest(Gallery::class, 6)),
                'latest_issue' => $this->latestIssue(),
                'prices' => $this->prices(),
                'live' => $this->live(),
            ],
        ]);
    }

    /**
     * Run a producer that returns a content collection and map to card arrays.
     */
    private function cards(callable $producer): array
    {
        try {
            $items = $producer();

            if ($items === null) {
                return [];
            }

            return NewsCardResource::collection($items)->toArray(request());
        } catch (\Throwable $e) {
            report($e);

            return [];
        }
    }

    /**
     * @param  class-string  $modelClass
     */
    private function latest(string $modelClass, int $limit)
    {
        return $modelClass::query()
            ->where('lang_id', 1)
            ->where('is_published', true)
            ->orderBy('id', 'DESC')
            ->take($limit)
            ->get();
    }

    /**
     * Service boxes: one box per top-level category with its latest news.
     */
    private function boxes(): array
    {
        try {
            if (! Schema::hasTable('categories')) {
                return [];
            }

            $categories = Category::whereNull('parent_id')->orderBy('id')->take(6)->get();

            $boxes = [];

            foreach ($categories as $category) {
                $ids = $category->children->pluck('id')->prepend($category->id);

                $news = News::query()
                    ->where('lang_id', 1)
                    ->where('is_published', true)
                    ->whereHas('categories', fn (Builder $q) => $q->whereIn('categories.id', $ids))
                    ->orderBy('id', 'DESC')
                    ->take(6)
                    ->get();

                if ($news->isEmpty()) {
                    continue;
                }

                $boxes[] = [
                    'title' => (string) ($category->title ?? ''),
                    'slug' => (string) ($category->slug ?? ''),
                    'items' => NewsCardResource::collection($news)->toArray(request()),
                ];
            }

            return $boxes;
        } catch (\Throwable $e) {
            report($e);

            return [];
        }
    }

    private function latestIssue(): ?array
    {
        try {
            if (! Schema::hasTable('archives')) {
                return null;
            }

            $issue = Archive::where('is_published', true)->orderBy('id', 'DESC')->first();

            if ($issue === null) {
                return null;
            }

            return (new PublicationResource($issue))->toArray(request());
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    private function prices(): array
    {
        try {
            if (! Schema::hasTable('market_prices')) {
                return [];
            }

            return PriceResource::collection(MarketPrice::activeGrouped()->flatten())->toArray(request());
        } catch (\Throwable $e) {
            report($e);

            return [];
        }
    }

    private function live(): array
    {
        try {
            if (! Schema::hasTable('live_streams')) {
                return [];
            }

            return LiveStreamResource::collection(LiveStream::activeStreams())->toArray(request());
        } catch (\Throwable $e) {
            report($e);

            return [];
        }
    }
}
