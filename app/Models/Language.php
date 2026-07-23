<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * زبان‌های سایت (WP-15 — چندزبانه).
 *
 * The languages table is never seeded: when it is empty (or does not exist
 * yet), fa (rtl, default) and en (ltr) are treated as built-in languages so
 * the site keeps working before/without any admin configuration.
 *
 * Content tables (news, notes, videos, ...) reference languages through the
 * integer lang_id column with the legacy convention 1 = fa, 2 = en (see
 * CONTENT_LANG_IDS). contentLangId() resolves a language code to that id.
 */
class Language extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'sort' => 'integer',
    ];

    /**
     * Legacy lang_id convention used by the content tables' lang_id column.
     */
    public const CONTENT_LANG_IDS = [
        'fa' => 1,
        'en' => 2,
    ];

    /**
     * Per-request cache so blades/partials can call the helpers repeatedly
     * without extra queries.
     */
    protected static ?Collection $activeCache = null;

    protected static ?bool $englishEnabledCache = null;

    /**
     * Built-in languages used while the languages table is empty/missing.
     * English is only marked active when the English site is enabled via
     * config('app.enable_english') so the language switcher stays hidden
     * until the English site actually exists.
     *
     * @return Collection<int, Language>
     */
    public static function builtIns(): Collection
    {
        $fa = new static([
            'name' => 'Persian',
            'native_name' => 'فارسی',
            'code' => 'fa',
            'direction' => 'rtl',
            'is_active' => true,
            'is_default' => true,
            'sort' => 1,
        ]);

        $en = new static([
            'name' => 'English',
            'native_name' => 'English',
            'code' => 'en',
            'direction' => 'ltr',
            'is_active' => (bool) config('app.enable_english', false),
            'is_default' => false,
            'sort' => 2,
        ]);

        return collect([$fa, $en]);
    }

    /**
     * Active languages ordered by sort. Schema-guarded: never throws and
     * never queries when the languages table does not exist yet (e.g. before
     * migrations run), falling back to the built-in fa/en pair.
     *
     * @return Collection<int, Language>
     */
    public static function active(): Collection
    {
        if (static::$activeCache !== null) {
            return static::$activeCache;
        }

        try {
            if (Schema::hasTable('languages') && static::query()->count() > 0) {
                return static::$activeCache = static::query()
                    ->where('is_active', true)
                    ->orderBy('sort')
                    ->orderBy('id')
                    ->get()
                    ->collect();
            }
        } catch (\Throwable $e) {
            // Database unavailable — fall through to built-ins.
        }

        return static::$activeCache = static::builtIns()
            ->filter(fn (Language $language) => $language->is_active)
            ->values();
    }

    /**
     * The default language (fa when nothing is configured).
     */
    public static function defaultLanguage(): Language
    {
        return static::active()->firstWhere('is_default', true)
            ?? static::active()->first()
            ?? static::builtIns()->first();
    }

    /**
     * Is the English site enabled? True when config('app.enable_english')
     * (env APP_ENABLE_ENGLISH) is on, OR the languages table says the "en"
     * language is active. Schema-guarded — safe to call from route files
     * before migrations have run.
     */
    public static function isEnglishEnabled(): bool
    {
        if (static::$englishEnabledCache !== null) {
            return static::$englishEnabledCache;
        }

        if ((bool) config('app.enable_english', false)) {
            return static::$englishEnabledCache = true;
        }

        try {
            if (Schema::hasTable('languages')) {
                return static::$englishEnabledCache = static::query()
                    ->where('code', 'en')
                    ->where('is_active', true)
                    ->exists();
            }
        } catch (\Throwable $e) {
            // Database unavailable — treat as disabled.
        }

        return static::$englishEnabledCache = false;
    }

    /**
     * Resolve a language code to the integer used by content lang_id
     * columns. Prefers the stored row id, falls back to the legacy 1=fa,
     * 2=en convention. Schema-guarded — never throws.
     */
    public static function contentLangId(string $code): int
    {
        try {
            if (Schema::hasTable('languages')) {
                $id = static::query()->where('code', $code)->value('id');

                if ($id !== null) {
                    return (int) $id;
                }
            }
        } catch (\Throwable $e) {
            // Fall through to the legacy convention.
        }

        return static::CONTENT_LANG_IDS[$code] ?? 1;
    }

    /**
     * URL of this language's home page (used by the header switcher).
     */
    public function homeUrl(): string
    {
        if ($this->code === 'en' && app('router')->has('website.ltr.home')) {
            return route('website.ltr.home');
        }

        return route('website.home');
    }
}
