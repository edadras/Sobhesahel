{{--
    Reusable SEO meta partial.

    Optional inputs (all degrade gracefully to site-wide defaults):
      - $seo (array): title, description, type, image, url, keywords, robots,
                      published_time, modified_time
      - $website_title (string): full <title> tag content
      - $page_title (string): used as meta title fallback when $seo['title'] is absent
--}}
@php
    $seoBrandName = trim((string) (setting('general.fa_brand_name') ?? '')) ?: 'گروه رسانه‌ای صبح‌ساحل';
    $seoDefaultDescription = 'پایگاه خبری صبح ساحل؛ تازه‌ترین اخبار هرمزگان، بندرعباس و ایران به همراه یادداشت، ویدئو، پادکست و گزارش تصویری.';

    $seoData = is_array($seo ?? null) ? $seo : [];

    $seoMetaTitle = trim(strip_tags((string) ($seoData['title'] ?? ($page_title ?? ''))));

    $seoTitleTag = trim(strip_tags((string) ($website_title ?? '')));
    if ($seoTitleTag === '') {
        $seoTitleTag = $seoMetaTitle !== '' ? $seoMetaTitle . ' | ' . $seoBrandName : $seoBrandName;
    }

    if ($seoMetaTitle === '') {
        $seoMetaTitle = $seoBrandName;
    }

    $seoMetaDescription = trim((string) preg_replace('/\s+/u', ' ', strip_tags((string) ($seoData['description'] ?? ''))));
    if ($seoMetaDescription === '') {
        $seoMetaDescription = $seoDefaultDescription;
    }
    $seoMetaDescription = \Illuminate\Support\Str::limit($seoMetaDescription, 300, '');

    $seoCanonicalUrl = (string) (($seoData['url'] ?? null) ?: url()->current());

    $seoOgType = in_array($seoData['type'] ?? null, ['website', 'article', 'profile'], true)
        ? $seoData['type']
        : 'website';

    $seoOgImage = (string) (($seoData['image'] ?? null) ?: 'asset/img/logo.png');
    if (!preg_match('#^https?://#i', $seoOgImage)) {
        $seoOgImage = url(ltrim($seoOgImage, '/'));
    }

    $seoRobots = (string) (($seoData['robots'] ?? null) ?: 'index,follow');
    $seoKeywords = trim((string) ($seoData['keywords'] ?? ''));
@endphp
<title>{{ $seoTitleTag }}</title>
<meta name="description" content="{{ $seoMetaDescription }}">
@if($seoKeywords !== '')
<meta name="keywords" content="{{ $seoKeywords }}">
@endif
<meta name="robots" content="{{ $seoRobots }}">
<meta name="language" content="fa-IR">
<link rel="canonical" href="{{ $seoCanonicalUrl }}">

{{-- Open Graph --}}
<meta property="og:site_name" content="{{ $seoBrandName }}">
<meta property="og:locale" content="fa_IR">
<meta property="og:type" content="{{ $seoOgType }}">
<meta property="og:title" content="{{ $seoMetaTitle }}">
<meta property="og:description" content="{{ $seoMetaDescription }}">
<meta property="og:url" content="{{ $seoCanonicalUrl }}">
<meta property="og:image" content="{{ $seoOgImage }}">
@if($seoOgType === 'article')
@if(!empty($seoData['published_time']))
<meta property="article:published_time" content="{{ $seoData['published_time'] }}">
@endif
@if(!empty($seoData['modified_time']))
<meta property="article:modified_time" content="{{ $seoData['modified_time'] }}">
@endif
@endif

{{-- Twitter Card --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:site" content="@sobhesahel">
<meta name="twitter:creator" content="@sobhesahel">
<meta name="twitter:domain" content="{{ parse_url(config('app.url'), PHP_URL_HOST) ?? 'sobhesahel.com' }}">
<meta name="twitter:title" content="{{ $seoMetaTitle }}">
<meta name="twitter:description" content="{{ $seoMetaDescription }}">
<meta name="twitter:image" content="{{ $seoOgImage }}">
