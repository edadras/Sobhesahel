<?php

namespace App\Support;

class AdvertisePositionConfig
{
    /**
     * Image advertise positions.
     *
     * Each key is the setting key that holds an array of advertise IDs
     * assigned to that position, and the value is the Persian label
     * shown in the admin panel.
     */
    public const IMAGE_POSITIONS = [
        'top_advertise_image' => 'تبلیغات تصویری منو بالا (سایدبار)',
        'button_advertise_image' => 'تبلیغات تصویری منو پایین (سایدبار)',
        'slider_advertise_image' => 'اسلایدر صفحه اصلی',
        'home_body_advertise_image' => 'بنر مابین بدنه صفحه اصلی',
        'single_page_advertise_image' => 'بنر صفحه داخلی (زیر متن مطلب)',
        'latest_news_advertise_image' => 'بنر زیر بلوک آخرین خبرها',
        'breaking_bar_advertise_image' => 'بنر زیر نوار خبر فوری',
    ];

    /**
     * Text advertise positions.
     */
    public const TEXT_POSITIONS = [
        'text_advertise' => 'تبلیغات متنی منو',
    ];

    public static function allPositionKeys(): array
    {
        return array_keys(self::IMAGE_POSITIONS + self::TEXT_POSITIONS);
    }
}
