<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Thin, fault-tolerant wrapper around the Matomo HTTP API.
 *
 * Every call is cached for ~60 seconds (including failures, so a downed
 * Matomo instance never slows the dashboard down with repeated timeouts).
 * All methods return null when Matomo is unconfigured or unreachable —
 * callers should render "—" placeholders in that case.
 */
class MatomoService
{
    /**
     * Cache TTL in seconds for Matomo API responses (successes and failures).
     */
    protected const CACHE_TTL = 60;

    /**
     * HTTP timeout in seconds — the dashboard must never hang on Matomo.
     */
    protected const TIMEOUT = 3;

    public static function isConfigured(): bool
    {
        return filled(config('analytics.matomo_url')) && filled(config('analytics.matomo_token'));
    }

    /**
     * Number of visitors active on the site in the last 5 minutes.
     */
    public static function realtimeVisitors(): ?int
    {
        $data = self::api('realtime', [
            'method' => 'Live.getCounters',
            'lastMinutes' => 5,
        ]);

        if (! is_array($data)) {
            return null;
        }

        return (int) ($data[0]['visitors'] ?? 0);
    }

    /**
     * Today's visit summary: ['visits' => int, 'unique' => int|null].
     */
    public static function todaySummary(): ?array
    {
        $data = self::api('today_summary', [
            'method' => 'VisitsSummary.get',
            'period' => 'day',
            'date' => 'today',
        ]);

        if (! is_array($data)) {
            return null;
        }

        return [
            'visits' => (int) ($data['nb_visits'] ?? 0),
            'unique' => isset($data['nb_uniq_visitors']) ? (int) $data['nb_uniq_visitors'] : null,
        ];
    }

    /**
     * Most viewed pages today: list of ['label' => string, 'hits' => int].
     */
    public static function topPages(int $limit = 10): ?array
    {
        $data = self::api('top_pages_'.$limit, [
            'method' => 'Actions.getPageTitles',
            'period' => 'day',
            'date' => 'today',
            'filter_limit' => $limit,
            'filter_sort_column' => 'nb_hits',
            'filter_sort_order' => 'desc',
        ]);

        if (! is_array($data)) {
            return null;
        }

        return collect($data)
            ->filter(fn ($row) => is_array($row) && isset($row['label']))
            ->map(fn ($row) => [
                'label' => trim((string) $row['label']),
                'hits' => (int) ($row['nb_hits'] ?? $row['nb_visits'] ?? 0),
            ])
            ->values()
            ->all();
    }

    /**
     * Today's referrer type breakdown: [persian label => visits].
     */
    public static function referrerTypes(): ?array
    {
        $data = self::api('referrer_types', [
            'method' => 'Referrers.getReferrerType',
            'period' => 'day',
            'date' => 'today',
        ]);

        if (! is_array($data)) {
            return null;
        }

        $labels = [
            'direct entry' => 'ورود مستقیم',
            'search engines' => 'موتورهای جستجو',
            'websites' => 'وب‌سایت‌ها',
            'social networks' => 'شبکه‌های اجتماعی',
            'campaigns' => 'کمپین‌ها',
        ];

        $result = [];

        foreach ($data as $row) {
            if (! is_array($row) || ! isset($row['label'])) {
                continue;
            }

            $label = $labels[strtolower((string) $row['label'])] ?? (string) $row['label'];
            $result[$label] = ($result[$label] ?? 0) + (int) ($row['nb_visits'] ?? 0);
        }

        return $result;
    }

    /**
     * Perform a cached, guarded call against the Matomo Reporting API.
     * Failures are cached too (as null payloads) to avoid repeated timeouts.
     */
    protected static function api(string $cacheKey, array $params): mixed
    {
        if (! self::isConfigured()) {
            return null;
        }

        try {
            $cached = Cache::remember('matomo.'.$cacheKey, self::CACHE_TTL, function () use ($params) {
                try {
                    $response = Http::connectTimeout(self::TIMEOUT)
                        ->timeout(self::TIMEOUT)
                        ->get(config('analytics.matomo_url'), array_merge([
                            'module' => 'API',
                            'idSite' => config('analytics.matomo_site_id', 1),
                            'format' => 'json',
                            'token_auth' => config('analytics.matomo_token'),
                        ], $params));

                    $json = $response->successful() ? $response->json() : null;

                    // Matomo returns {"result":"error", ...} with HTTP 200 on bad tokens.
                    if (is_array($json) && ($json['result'] ?? null) === 'error') {
                        $json = null;
                    }

                    // Wrap so that failures (null) are cached as well.
                    return ['payload' => $json];
                } catch (\Throwable $e) {
                    return ['payload' => null];
                }
            });

            return $cached['payload'] ?? null;
        } catch (\Throwable $e) {
            // Cache store failure etc. — never break the dashboard.
            return null;
        }
    }
}
