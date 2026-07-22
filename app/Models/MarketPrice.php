<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class MarketPrice extends Model
{
    public const CACHE_KEY = 'market_prices.active_grouped';

    /**
     * Price categories with their Persian labels (display order matters).
     */
    public const CATEGORIES = [
        'currency' => 'ارز',
        'gold' => 'طلا',
        'coin' => 'سکه',
        'crypto' => 'ارز دیجیتال',
        'market' => 'بازار',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'price' => 'decimal:4',
        'change_amount' => 'decimal:4',
        'change_percent' => 'decimal:2',
        'is_active' => 'boolean',
        'fetched_at' => 'datetime',
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

    /**
     * Active prices grouped by category, ordered like self::CATEGORIES.
     *
     * @return Collection<string, Collection<int, self>>
     */
    public static function activeGrouped(): Collection
    {
        return Cache::remember(self::CACHE_KEY, 600, function () {
            return self::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->groupBy('category')
                ->sortBy(fn ($items, $category) => array_search($category, array_keys(self::CATEGORIES)));
        });
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    /**
     * Direction of the last change: 1 = up, -1 = down, 0 = unchanged/unknown.
     */
    public function getTrendAttribute(): int
    {
        $change = (float) ($this->change_amount ?? 0);

        if ($change == 0.0) {
            $change = (float) ($this->change_percent ?? 0);
        }

        return $change > 0 ? 1 : ($change < 0 ? -1 : 0);
    }

    /**
     * Price formatted with thousands separator and Persian digits.
     */
    public function getFormattedPriceAttribute(): string
    {
        return self::toFarsiNumber(self::trimZeros($this->price));
    }

    public function getFormattedChangePercentAttribute(): ?string
    {
        if ($this->change_percent === null) {
            return null;
        }

        return self::toFarsiNumber(self::trimZeros(abs((float) $this->change_percent), false)) . '٪';
    }

    /**
     * Format a decimal, dropping meaningless trailing zeros ("48500.0000" -> "48,500").
     */
    public static function trimZeros($value, bool $thousands = true): string
    {
        $value = (float) $value;
        $decimals = ($value == floor($value)) ? 0 : 2;

        return number_format($value, $decimals, '.', $thousands ? ',' : '');
    }

    /**
     * Convert latin digits to Persian digits (site-wide display convention).
     */
    public static function toFarsiNumber($value): string
    {
        $latin = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

        return str_replace($latin, $persian, (string) $value);
    }
}
