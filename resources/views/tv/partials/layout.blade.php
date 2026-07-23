{{--
    صبح ساحل تی‌وی — dark, video-first layout for the TV subdomain.
    Reuses site assets (bootstrap + fonts) plus the dedicated tv.css.
    Expects (all optional / guarded): $seo, $website_title, $on_tv_domain
--}}
@php
    $tvIsDomain = (bool) ($on_tv_domain ?? false);

    // Nav links: real TV routes on the subdomain; safe main-domain fallbacks
    // when the TV home is viewed through the /tv alias (no DNS required).
    $tvNav = [
        'home' => $tvIsDomain ? route('tv.home') : route('tv.alias.home'),
        'videos' => $tvIsDomain ? route('tv.videos') : route('website.rtl.index', ['type' => 'video']),
        'live' => $tvIsDomain ? route('tv.live') : route('website.rtl.live'),
    ];

    $tvMainSiteUrl = rtrim((string) config('app.url'), '/') ?: url('/');
@endphp
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>

    {{-- SEO: title, description, canonical (points to the TV domain), OG, Twitter --}}
    @include('website.components.seo-meta')

    <link rel="icon" href="{{ asset('asset/img/logo.png') }}"/>

    {{-- Reuse existing site assets --}}
    <link rel="stylesheet" href="{{ asset('asset/css/bootstrap.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('asset/css/main_font.css') }}"/>
    {{-- Dedicated TV identity --}}
    <link rel="stylesheet" href="{{ asset('asset/css/tv.css') }}"/>

    @yield('style')
</head>
<body class="tvBody">

<header class="tvHeader">
    <div class="container">
        <div class="tvHeaderIn">
            <a href="{{ $tvNav['home'] }}" class="tvLogo">
                صبح ساحل <span>تی‌وی</span>
            </a>

            <nav class="tvNav">
                <a href="{{ $tvNav['home'] }}">خانه</a>
                <a href="{{ $tvNav['videos'] }}">ویدئوها</a>
                <a href="{{ $tvNav['live'] }}" class="tvNavLive">
                    <span class="tvLiveDot"></span>
                    پخش زنده
                </a>
            </nav>

            <a href="{{ $tvMainSiteUrl }}" class="tvBackMain">سایت اصلی صبح ساحل</a>
        </div>
    </div>
</header>

<main class="tvMain">
    @yield('content')
</main>

<footer class="tvFooter">
    <div class="container">
        <p>© {{ \App\Models\MarketPrice::toFarsiNumber(\Morilog\Jalali\Jalalian::now()->getYear()) }}
            صبح ساحل تی‌وی — گروه رسانه‌ای صبح‌ساحل</p>
        <a href="{{ $tvMainSiteUrl }}">بازگشت به سایت اصلی</a>
    </div>
</footer>

@yield('script')
</body>
</html>
