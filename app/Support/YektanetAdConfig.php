<?php

namespace App\Support;

class YektanetAdConfig
{
    public const POSITIONS = [
        'head' => 'داخل head (اسکریپت)',
        'header' => 'زیر هدر',
        'sidebar_top' => 'بالای سایدبار',
        'sidebar_bottom' => 'پایین سایدبار',
        'content_top' => 'بالای محتوا',
        'content_bottom' => 'پایین محتوا',
        'footer' => 'بالای فوتر',
        'body_end' => 'انتهای body',
    ];

    public const PAGE_SCOPES = [
        'all' => 'همه صفحات',
        'home' => 'صفحه اول',
        'news_single' => 'صفحه خبر',
        'note_single' => 'صفحه مقاله',
        'podcast_single' => 'صفحه پادکست',
        'video_single' => 'صفحه ویدیو',
        'photo_single' => 'صفحه گالری',
        'news_list' => 'لیست اخبار',
        'note_list' => 'لیست مقالات',
        'podcast_list' => 'لیست پادکست',
        'video_list' => 'لیست ویدیو',
        'photo_list' => 'لیست عکس',
        'category' => 'صفحه دسته‌بندی',
        'tag' => 'صفحه برچسب',
        'search' => 'صفحه جستجو',
        'contact' => 'تماس با ما',
        'about' => 'درباره ما',
        'archive' => 'آرشیو PDF',
        'author' => 'صفحه نویسنده',
    ];
}
