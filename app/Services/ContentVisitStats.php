<?php

namespace App\Services;

use App\Models\Gallery;
use App\Models\News;
use App\Models\Note;
use App\Models\Podcast;
use App\Models\Video;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Internal (database-backed) visit statistics, aggregated from the `visits`
 * counter on published content across all content types.
 *
 * Every query is guarded — on an empty table (or a missing one during
 * deployment) methods simply return zeros instead of throwing.
 */
class ContentVisitStats
{
    /**
     * Content models that carry a `visits` counter and `publish_at` column.
     *
     * @var array<class-string, string> model => Persian label
     */
    public const CONTENT_MODELS = [
        News::class => 'اخبار',
        Note::class => 'یادداشت‌ها',
        Video::class => 'ویدیوها',
        Podcast::class => 'پادکست‌ها',
        Gallery::class => 'گالری‌ها',
    ];

    /**
     * Total visits of content published on/after the given date.
     */
    public static function visitsSince(Carbon $since): int
    {
        $total = 0;

        foreach (array_keys(self::CONTENT_MODELS) as $model) {
            try {
                $total += (int) $model::query()
                    ->whereNotNull('publish_at')
                    ->where('publish_at', '>=', $since)
                    ->sum('visits');
            } catch (\Throwable $e) {
                // Table missing or query failure — treat as zero.
            }
        }

        return $total;
    }

    /**
     * Per-day visit sums (by publish date) for the last N days, oldest first.
     *
     * @return array<string, int> keyed by Y-m-d
     */
    public static function dailyVisits(int $days = 14): array
    {
        try {
            return Cache::remember("content-visit-stats.daily.{$days}", 300, function () use ($days) {
                $from = Carbon::today()->subDays($days - 1);

                $totals = [];
                for ($i = $days - 1; $i >= 0; $i--) {
                    $totals[Carbon::today()->subDays($i)->format('Y-m-d')] = 0;
                }

                foreach (array_keys(self::CONTENT_MODELS) as $model) {
                    try {
                        $rows = $model::query()
                            ->whereNotNull('publish_at')
                            ->where('publish_at', '>=', $from)
                            ->selectRaw('DATE(publish_at) as day, SUM(visits) as total')
                            ->groupBy('day')
                            ->pluck('total', 'day');

                        foreach ($rows as $day => $total) {
                            if (isset($totals[$day])) {
                                $totals[$day] += (int) $total;
                            }
                        }
                    } catch (\Throwable $e) {
                        // Ignore this model on failure.
                    }
                }

                return $totals;
            });
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Total visits per content type: [persian label => visits].
     *
     * @return array<string, int>
     */
    public static function visitsByType(): array
    {
        $result = [];

        foreach (self::CONTENT_MODELS as $model => $label) {
            try {
                $result[$label] = (int) $model::query()->sum('visits');
            } catch (\Throwable $e) {
                $result[$label] = 0;
            }
        }

        return $result;
    }

    /**
     * Most viewed content items across all types.
     *
     * @return \Illuminate\Support\Collection<int, array{title: string, type: string, visits: int, publish_at: mixed}>
     */
    public static function mostViewed(int $limit = 10): \Illuminate\Support\Collection
    {
        $items = collect();

        foreach (self::CONTENT_MODELS as $model => $label) {
            try {
                $rows = $model::query()
                    ->orderByDesc('visits')
                    ->limit($limit)
                    ->get(['title', 'visits', 'publish_at'])
                    ->map(fn ($row) => [
                        'title' => (string) $row->title,
                        'type' => $label,
                        'visits' => (int) $row->visits,
                        'publish_at' => $row->publish_at,
                    ]);

                $items = $items->merge($rows);
            } catch (\Throwable $e) {
                // Skip this model on failure.
            }
        }

        return $items->sortByDesc('visits')->take($limit)->values();
    }
}
