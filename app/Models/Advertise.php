<?php

namespace App\Models;

use App\Support\AdvertisePositionConfig;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Outerweb\Settings\Models\Setting;

class Advertise extends Model
{
    use HasFactory;

    /**
     * Cache key holding the list of pending buffered stat keys.
     * Buffered impressions/clicks are flushed to `advertise_daily_stats`
     * by the `app:advertise-flush-stats` command (scheduled every 10 minutes),
     * so rendering a placement never writes to the database directly.
     */
    public const STATS_INDEX_KEY = 'advertise_stats:index';

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /** @var array<string,bool> runtime cache for schema checks */
    protected static array $schemaChecks = [];

    protected static function booted(): void
    {
        static::deleted(function (Advertise $advertise) {
            $advertise->removeFromAllPositions();
        });
    }

    public function events()
    {
        return $this->hasMany(AdvertiseEvent::class);
    }

    public function dailyStats()
    {
        return $this->hasMany(AdvertiseDailyStat::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Schema guards (rendering may run before the tracking migration)
    |--------------------------------------------------------------------------
    */

    public static function tableReady(string $table): bool
    {
        if (! array_key_exists($table, self::$schemaChecks)) {
            try {
                self::$schemaChecks[$table] = Schema::hasTable($table);
            } catch (\Throwable $e) {
                self::$schemaChecks[$table] = false;
            }
        }

        return self::$schemaChecks[$table];
    }

    public static function hasSchedulingColumns(): bool
    {
        if (! array_key_exists('_scheduling', self::$schemaChecks)) {
            try {
                self::$schemaChecks['_scheduling'] = Schema::hasColumn('advertises', 'starts_at');
            } catch (\Throwable $e) {
                self::$schemaChecks['_scheduling'] = false;
            }
        }

        return self::$schemaChecks['_scheduling'];
    }

    /*
    |--------------------------------------------------------------------------
    | Display eligibility (scheduling window + click/view limits)
    |--------------------------------------------------------------------------
    */

    /**
     * Constrain a query to ads that are currently allowed to be displayed:
     * active, inside their [starts_at, ends_at] window and under their
     * max_views / max_clicks limits.
     */
    public static function applyDisplayConstraints(Builder $query): Builder
    {
        $query->where('is_active', true);

        if (self::hasSchedulingColumns()) {
            $now = now();

            $query
                ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
                ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
                ->where(fn (Builder $q) => $q->whereNull('max_views')->orWhere('max_views', 0)->orWhereColumn('view', '<', 'max_views'))
                ->where(fn (Builder $q) => $q->whereNull('max_clicks')->orWhere('max_clicks', 0)->orWhereColumn('click', '<', 'max_clicks'));
        }

        return $query;
    }

    /**
     * Whether this ad may be displayed / clicked right now.
     */
    public function isDisplayable(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if (self::hasSchedulingColumns()) {
            if ($this->starts_at !== null && $this->starts_at->isFuture()) {
                return false;
            }

            if ($this->ends_at !== null && $this->ends_at->isPast()) {
                return false;
            }

            if ((int) $this->max_views > 0 && (int) $this->view >= (int) $this->max_views) {
                return false;
            }

            if ((int) $this->max_clicks > 0 && (int) $this->click >= (int) $this->max_clicks) {
                return false;
            }
        }

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Retrieval helpers used by the website placements
    |--------------------------------------------------------------------------
    */

    /**
     * Get one random eligible image advertise for the given position.
     * Buffers an impression and rewrites `url` to the click-tracking route.
     */
    public static function getImageAdvertise($title, $lang_id = 1)
    {
        try {
            if (! self::tableReady('advertises')) {
                return null;
            }

            $data = setting($title);

            if ($data == null || count($data) == 0) {
                return null;
            }

            $query = self::whereIn('id', $data)->whereNotNull('image');

            if (self::hasSchedulingColumns()) {
                // Video ads only serve through the VAST endpoint.
                $query->where(fn (Builder $q) => $q->whereNull('type')->orWhere('type', '!=', 'video'));
            }

            $ads = self::applyDisplayConstraints($query)->get();

            if ($ads->isEmpty()) {
                return null;
            }

            // نمایش تصادفی: pick randomly (equal weight) among eligible ads.
            $ad = $ads->random();

            self::bufferStat((int) $ad->id, 'view');

            $ad->url = route('advertise_click', ['id' => $ad->id]);

            return $ad;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Get all eligible text advertises. Buffers one impression per ad.
     */
    public static function getTextAdvertise($lang_id = 1)
    {
        try {
            if (! self::tableReady('advertises')) {
                return null;
            }

            $data = setting('text_advertise');

            if ($data == null || count($data) == 0) {
                return null;
            }

            $advertises = self::applyDisplayConstraints(self::whereIn('id', $data))
                ->inRandomOrder()
                ->get();

            foreach ($advertises as $advertise) {
                self::bufferStat((int) $advertise->id, 'view');
            }

            return $advertises;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Click / impression tracking
    |--------------------------------------------------------------------------
    */

    /**
     * Insert a raw event row (type: click|view) into advertise_events.
     */
    public static function recordEvent(int $advertiseId, string $type, ?string $ip = null, ?string $userAgent = null): void
    {
        try {
            if (! self::tableReady('advertise_events')) {
                return;
            }

            DB::table('advertise_events')->insert([
                'advertise_id' => $advertiseId,
                'type' => $type,
                'ip' => $ip !== null ? substr($ip, 0, 45) : null,
                'user_agent_hash' => $userAgent !== null ? hash('sha256', $userAgent) : null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Tracking must never break rendering or redirects.
        }
    }

    /**
     * Buffer a view/click count in cache (no DB write per pageview).
     * Flushed by app:advertise-flush-stats into advertise_daily_stats.
     */
    public static function bufferStat(int $advertiseId, string $type): void
    {
        try {
            $key = 'advertise_stats:' . $type . ':' . $advertiseId . ':' . now()->toDateString();

            Cache::add($key, 0, now()->addDays(2));
            Cache::increment($key);

            $index = (array) Cache::get(self::STATS_INDEX_KEY, []);

            if (! in_array($key, $index, true)) {
                $index[] = $key;
                Cache::put(self::STATS_INDEX_KEY, $index, now()->addDays(2));
            }
        } catch (\Throwable $e) {
            // Never let stats buffering break the page.
        }
    }

    /**
     * Flush buffered counters into advertise_daily_stats + the aggregate
     * view/click columns, then deactivate ads that passed their limits.
     *
     * @return array{views:int,clicks:int,deactivated:int}
     */
    public static function flushBufferedStats(): array
    {
        $summary = ['views' => 0, 'clicks' => 0, 'deactivated' => 0];

        if (! self::tableReady('advertises')) {
            return $summary;
        }

        try {
            $index = (array) Cache::pull(self::STATS_INDEX_KEY, []);
        } catch (\Throwable $e) {
            return $summary;
        }

        foreach ($index as $key) {
            if (! is_string($key) || ! preg_match('/^advertise_stats:(view|click):(\d+):(\d{4}-\d{2}-\d{2})$/', $key, $matches)) {
                continue;
            }

            $count = (int) Cache::pull($key, 0);

            if ($count < 1) {
                continue;
            }

            [, $type, $advertiseId, $date] = $matches;
            $advertiseId = (int) $advertiseId;
            $column = $type === 'view' ? 'views' : 'clicks';

            try {
                if (self::tableReady('advertise_daily_stats')) {
                    $updated = DB::table('advertise_daily_stats')
                        ->where('advertise_id', $advertiseId)
                        ->where('date', $date)
                        ->increment($column, $count, ['updated_at' => now()]);

                    if ($updated === 0) {
                        DB::table('advertise_daily_stats')->insert([
                            'advertise_id' => $advertiseId,
                            'date' => $date,
                            $column => $count,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                // Aggregate totals (used for limit enforcement + reports).
                DB::table('advertises')
                    ->where('id', $advertiseId)
                    ->increment($type === 'view' ? 'view' : 'click', $count);

                $summary[$column] += $count;
            } catch (\Throwable $e) {
                continue;
            }
        }

        // Deactivate ads that reached their max view/click limits.
        if (self::hasSchedulingColumns()) {
            try {
                $summary['deactivated'] = self::where('is_active', true)
                    ->where(function (Builder $query) {
                        $query
                            ->where(fn (Builder $q) => $q->whereNotNull('max_views')->where('max_views', '>', 0)->whereColumn('view', '>=', 'max_views'))
                            ->orWhere(fn (Builder $q) => $q->whereNotNull('max_clicks')->where('max_clicks', '>', 0)->whereColumn('click', '>=', 'max_clicks'));
                    })
                    ->update(['is_active' => false]);
            } catch (\Throwable $e) {
                // ignore
            }
        }

        return $summary;
    }

    /*
    |--------------------------------------------------------------------------
    | Video / VAST helpers
    |--------------------------------------------------------------------------
    */

    public function isVideo(): bool
    {
        return ($this->type ?? null) === 'video';
    }

    /**
     * Absolute URL of the video media file (external URL wins over upload).
     */
    public function videoSource(): ?string
    {
        if (! empty($this->video_url)) {
            return $this->video_url;
        }

        if (! empty($this->video)) {
            return url(Storage::url($this->video));
        }

        return null;
    }

    /**
     * Video duration formatted as HH:MM:SS for the VAST <Duration> node.
     */
    public function vastDuration(): string
    {
        $seconds = (int) ($this->video_duration ?? 0);

        if ($seconds < 1) {
            $seconds = 30;
        }

        return gmdate('H:i:s', $seconds);
    }

    /*
    |--------------------------------------------------------------------------
    | Position assignment (settings-backed)
    |--------------------------------------------------------------------------
    */

    /**
     * Setting keys of the positions this advertise is currently assigned to.
     */
    public static function getPositionsForAd($adId): array
    {
        if ($adId == null) {
            return [];
        }

        $positions = [];

        foreach (AdvertisePositionConfig::allPositionKeys() as $key) {
            $ids = array_map('intval', (array) (setting($key) ?? []));

            if (in_array((int) $adId, $ids, true)) {
                $positions[] = $key;
            }
        }

        return $positions;
    }

    /**
     * Assign this advertise to the given position keys (and remove it
     * from every other position of the same type).
     */
    public function syncPositions(?array $positions): void
    {
        $positions = $positions ?? [];

        if ($this->isVideo()) {
            // Video ads only serve through the VAST endpoint; make sure they
            // are not assigned to any banner/text position.
            $this->removeFromAllPositions();

            return;
        }

        $isText = ($this->type ?? null) === 'text' || (($this->type ?? null) === null && $this->image == null);

        $available = $isText
            ? array_keys(AdvertisePositionConfig::TEXT_POSITIONS)
            : array_keys(AdvertisePositionConfig::IMAGE_POSITIONS);

        foreach ($available as $key) {
            $ids = array_map('intval', (array) (setting($key) ?? []));
            $ids = array_values(array_diff($ids, [(int) $this->id]));

            if (in_array($key, $positions, true)) {
                $ids[] = (int) $this->id;
            }

            Setting::set($key, array_values(array_unique($ids)));
        }
    }

    /**
     * Remove this advertise from every position it is assigned to.
     */
    public function removeFromAllPositions(): void
    {
        foreach (AdvertisePositionConfig::allPositionKeys() as $key) {
            $ids = array_map('intval', (array) (setting($key) ?? []));
            $filtered = array_values(array_diff($ids, [(int) $this->id]));

            if (count($filtered) !== count($ids)) {
                Setting::set($key, $filtered);
            }
        }
    }

    public function delete_advertise()
    {
        try {
            // Positions are cleaned up by the "deleted" model event.
            $this->delete();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Deactivate this ad when it passed its click/view limits.
     * (Also handled in bulk by app:advertise-flush-stats.)
     */
    public function handle_advertise_cronjob()
    {
        if ((int) $this->max_views > 0 && (int) $this->view >= (int) $this->max_views) {
            $this->update(['is_active' => false]);
        }

        if ((int) $this->max_clicks > 0 && (int) $this->click >= (int) $this->max_clicks) {
            $this->update(['is_active' => false]);
        }
    }
}
