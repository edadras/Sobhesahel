<?php

namespace App\Support;

use App\Models\Gallery;
use App\Models\News;
use App\Models\Note;
use App\Models\Podcast;
use App\Models\Video;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Morilog\Jalali\Jalalian;

/**
 * Shared helpers for the public News API (app/Http/Controllers/Api/V1 and
 * app/Http/Resources/Api). Everything here is null-safe and never throws, so
 * a partial/broken row can never turn a public response into a 500.
 */
class ApiContent
{
    /**
     * Public content types (app-facing) mapped to their Eloquent models.
     * `photo` is the app-facing name for the Gallery model.
     */
    public const MODELS = [
        'news' => News::class,
        'note' => Note::class,
        'video' => Video::class,
        'podcast' => Podcast::class,
        'photo' => Gallery::class,
    ];

    /**
     * Resolve a content-type string to its model class, or null when unknown.
     */
    public static function modelClass(string $type): ?string
    {
        $type = strtolower(trim($type));

        if ($type === 'gallery' || $type === 'image') {
            $type = 'photo';
        }

        return self::MODELS[$type] ?? null;
    }

    /**
     * The app-facing type string for a content model instance.
     */
    public static function typeOf(Model $model): string
    {
        $base = strtolower(class_basename($model));

        return $base === 'gallery' ? 'photo' : $base;
    }

    /**
     * Make a stored path/URL absolute. Already-absolute URLs are kept as-is;
     * empty values become null so the app shows its own placeholder.
     */
    public static function abs(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        if (preg_match('#^(https?:)?//#i', $path)) {
            return $path;
        }

        try {
            return url($path);
        } catch (\Throwable $e) {
            return $path;
        }
    }

    /**
     * Absolute image URL for a content model (large size), or null.
     */
    public static function imageUrl(Model $model, string $size = 'large'): ?string
    {
        try {
            if (method_exists($model, 'getImageUrl')) {
                return self::abs($model->getImageUrl($size));
            }
        } catch (\Throwable $e) {
            // fall through
        }

        return null;
    }

    /**
     * Jalali date-time ("H:i Y/m/d") — matches ContentMetaDataResource.
     */
    public static function jalali($date): ?string
    {
        if (empty($date)) {
            return null;
        }

        try {
            return Jalalian::fromCarbon(Carbon::parse($date))->format('H:i Y/m/d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Jalali date ("d MonthName Y" with Persian digits), used for humans-only
     * fields like comment/publication dates.
     */
    public static function jalaliLong($date): ?string
    {
        if (empty($date)) {
            return null;
        }

        try {
            return Jalalian::fromCarbon(Carbon::parse($date))->format('%d %B %Y');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * ISO-8601 timestamp, or null.
     */
    public static function iso($date): ?string
    {
        if (empty($date)) {
            return null;
        }

        try {
            return Carbon::parse($date)->toIso8601String();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
