<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Morilog\Jalali\Jalalian;

class FeaturedNews extends Model
{
    const BOXES = [
        'top_slider' => [
            'title' => 'اسلایدر بالا',
            'max_items' => 12,
        ],
        'button' => [
            'title' => '۳ پست پایین',
            'max_items' => 3,
        ],
        'urgent' => [
            'title' => 'اخبار فوری',
            'max_items' => 5,
        ],
    ];

    /**
     * Get news IDs by section
     *
     * @param string $section
     * @return array
     */
    public static function getNewsIdsBySection($section)
    {
        $query = static::active($section)
            ->select('news_id');

        if (array_key_exists($section, self::BOXES)) {
            $query->take(self::BOXES[$section]['max_items'] ?? 10);
        }

        return $query->pluck('news_id')->toArray();
    }

    protected $fillable = ['news_id', 'section', 'expires_at', 'display_order'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Relationship to News model
     */
    public function news()
    {
        return $this->belongsTo(News::class);
    }

    /**
     * Scope to get active (non-expired) featured news for a section
     */
    public function scopeActive($query, $section = 'top')
    {
        return $query->where('section', $section)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
//            ->orderBy('display_order')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Scope to get featured news for frontend display
     */
    public function scopeForSection($query, $section)
    {
        if (!array_key_exists($section, self::BOXES)) {
            return $query->whereNull('id'); // Return empty query if section is invalid
        }

        return $query->active($section)
            ->with('news')
            ->take(self::BOXES[$section]['max_items'] ?? 10);
    }

    /**
     * Get news articles by box title
     *
     * @param string $boxTitle
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getNewsByBoxTitle($boxTitle)
    {
        // Find the section key by box title
        $section = null;
        foreach (self::BOXES as $key => $box) {
            if ($key === $boxTitle) {
                $section = $key;
                break;
            }
        }

        // If no matching section found, return empty collection
        if (!$section) {
            return News::whereNull('id')->get();
        }

        // Query active featured news for the section and return related news
        return News::whereIn('id', function ($query) use ($section) {
            $query->select('news_id')
                ->from('featured_news')
                ->where('section', $section)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>=', now());
                });
        })
            ->orderByRaw('FIELD(id, ' . self::active($section)->pluck('news_id')->implode(',') . ')')
            ->take(self::BOXES[$section]['max_items'] ?? 10)
            ->get();
    }

    /**
     * Clean expired featured news entries
     */
    public static function cleanExpired()
    {
        return static::whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->delete();
    }

    /**
     * Convert Jalali date to Gregorian for storage
     *
     * @param string $jalaliDate Format: Y-m-d H:i
     * @return \Carbon\Carbon|null
     */
    public static function convertJalaliToGregorian($jalaliDate)
    {
        if (empty($jalaliDate) || !is_string($jalaliDate)) {
            return null;
        }

        // Convert Persian/Arabic numbers to English
        $persianNumbers = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $jalaliDate = str_replace($persianNumbers, $englishNumbers, $jalaliDate);

        if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $jalaliDate)) {
            return null;
        }

        try {
            return Jalalian::fromFormat('Y-m-d H:i', $jalaliDate)->toCarbon();
        } catch (\Exception $e) {
            \Log::warning("Invalid Jalali date format: {$jalaliDate}, Error: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Get Jalali formatted expires_at attribute
     *
     * @return string|null
     */
    public function getJalaliExpiresAtAttribute()
    {
        return $this->expires_at
            ? Jalalian::fromCarbon($this->expires_at)->format('Y-m-d H:i')
            : null;
    }
}
