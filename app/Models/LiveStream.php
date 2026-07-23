<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Morilog\Jalali\Jalalian;

class LiveStream extends Model
{
    public const CACHE_KEY = 'live_streams.active';

    /**
     * Supported platforms with their Persian labels.
     */
    public const PLATFORMS = [
        'aparat' => 'آپارات',
        'youtube' => 'یوتیوب',
        'instagram' => 'اینستاگرام',
        'custom' => 'سایر',
    ];

    /**
     * Allowed embed hosts per platform. Only a URL is ever stored
     * (never raw HTML) and the iframe is built server-side after the
     * host is validated against this whitelist.
     */
    public const PLATFORM_HOSTS = [
        'aparat' => ['aparat.com', 'www.aparat.com'],
        'youtube' => ['youtube.com', 'www.youtube.com', 'youtube-nocookie.com', 'www.youtube-nocookie.com', 'youtu.be'],
        'instagram' => ['instagram.com', 'www.instagram.com'],
        'custom' => [],
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'is_live' => 'boolean',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'sort' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => self::clearCache());
        static::deleted(fn () => self::clearCache());
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Active streams, live-now first, then by sort / upcoming start time.
     * Cached briefly; used by the /live pages and the header indicator.
     *
     * @return Collection<int, self>
     */
    public static function activeStreams(): Collection
    {
        return Cache::remember(self::CACHE_KEY, 120, function () {
            return self::query()
                ->active()
                ->orderByDesc('is_live')
                ->orderBy('sort')
                ->orderByRaw('starts_at IS NULL, starts_at ASC')
                ->orderBy('id')
                ->get();
        });
    }

    /**
     * Is at least one active stream broadcasting right now?
     * Null-safe: never throws (e.g. before the migration has run).
     */
    public static function hasLiveNow(): bool
    {
        try {
            return self::activeStreams()->contains(fn ($stream) => (bool) $stream->is_live);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function getPlatformLabelAttribute(): string
    {
        return self::PLATFORMS[$this->platform] ?? $this->platform;
    }

    /**
     * Validate the stored URL: http(s) only and, for known platforms,
     * the host must match the platform's whitelist.
     */
    public function isEmbeddable(): bool
    {
        return self::validateUrlForPlatform((string) $this->embed_url, (string) $this->platform);
    }

    public static function validateUrlForPlatform(string $url, string $platform): bool
    {
        $parts = parse_url(trim($url));

        if (!is_array($parts) || empty($parts['host']) || empty($parts['scheme'])) {
            return false;
        }

        if (!in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            return false;
        }

        $hosts = self::PLATFORM_HOSTS[$platform] ?? null;

        if ($hosts === null) {
            // Unknown platform value — refuse to embed.
            return false;
        }

        if ($hosts === []) {
            // "custom" platform: any valid http(s) host is allowed.
            return true;
        }

        return in_array(strtolower($parts['host']), $hosts, true);
    }

    /**
     * Server-side iframe src built from the stored URL (never raw HTML).
     * Returns null when the URL fails validation, so callers can skip it.
     */
    public function getEmbedSrcAttribute(): ?string
    {
        if (!$this->isEmbeddable()) {
            return null;
        }

        $url = trim((string) $this->embed_url);
        $parts = parse_url($url);
        $path = $parts['path'] ?? '';

        if ($this->platform === 'youtube') {
            // Already an embed URL — keep it.
            if (str_contains($path, '/embed')) {
                return $url;
            }

            // https://youtu.be/{id}
            if (strtolower($parts['host']) === 'youtu.be' && preg_match('#^/([A-Za-z0-9_-]{5,20})#', $path, $m)) {
                return 'https://www.youtube.com/embed/' . $m[1];
            }

            // https://www.youtube.com/watch?v={id}
            parse_str($parts['query'] ?? '', $query);
            if (!empty($query['v']) && preg_match('/^[A-Za-z0-9_-]{5,20}$/', $query['v'])) {
                return 'https://www.youtube.com/embed/' . $query['v'];
            }

            // https://www.youtube.com/live/{id}
            if (preg_match('#/live/([A-Za-z0-9_-]{5,20})#', $path, $m)) {
                return 'https://www.youtube.com/embed/' . $m[1];
            }

            return $url;
        }

        if ($this->platform === 'aparat') {
            if (str_contains($path, '/embed') || str_contains($path, '/videohash/')) {
                return $url;
            }

            // https://www.aparat.com/v/{hash}
            if (preg_match('#/v/([A-Za-z0-9]+)#', $path, $m)) {
                return 'https://www.aparat.com/video/video/embed/videohash/' . $m[1] . '/vt/frame';
            }

            // Live channel pages embed fine as-is (host already validated).
            return $url;
        }

        if ($this->platform === 'instagram') {
            if (str_contains($path, '/embed')) {
                return $url;
            }

            // Post / reel / live URLs accept the /embed suffix.
            $base = strtok($url, '?');

            return rtrim($base === false ? $url : $base, '/') . '/embed';
        }

        // custom: validated http(s) URL, embed as-is.
        return $url;
    }

    /**
     * Jalali start date-time with Persian digits, or null.
     */
    public function getStartsAtJalaliAttribute(): ?string
    {
        if (empty($this->starts_at)) {
            return null;
        }

        try {
            $formatted = Jalalian::fromCarbon(Carbon::parse($this->starts_at))->format('%d %B %Y ساعت H:i');

            return MarketPrice::toFarsiNumber($formatted);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
