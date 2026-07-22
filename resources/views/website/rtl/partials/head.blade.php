<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="utf-8"/>
    <title>{{ $website_title ?? setting('general.fa_brand_name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta property="og:title" content="{{ $seo['title'] ?? 'گروه رسانه‌ای صبح‌ساحل' }}">
    <meta property="og:description" content="{{ $seo['description'] ?? 'گروه رسانه‌ای صبح‌ساحل' }}">
    <meta property="og:type" content="{{ $seo['type'] ?? 'article' }}">
    <meta property="og:site_name" content="گروه رسانه‌ای صبح‌ساحل">
    <meta property="og:locale" content="fa_IR">
    <meta property="og:url" content="{{ request()->fullUrl() }}">
    @if(isset($seo['image']))
    <meta property="og:image" content="{{ $seo['image'] }}">
    @endif

    <link rel="manifest" href="/manifest.json">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@sobhesahel">
    <meta name="twitter:title" content="{{ $seo['title'] ?? 'گروه رسانه‌ای صبح‌ساحل' }}">
    <meta name="twitter:description" content="{{ $seo['description'] ?? 'گروه رسانه‌ای صبح‌ساحل' }}">
    <meta name="twitter:domain" content="sobhesahel.com">
    <meta name="twitter:creator" content="@sobhesahel">

    <!-- SEO Meta -->
    <meta name="description" content="{{ $seo['description'] ?? 'گروه رسانه‌ای صبح‌ساحل' }}">
    <meta name="keywords" content="{{ $seo['keywords'] ?? '' }}">
    <meta name="robot" content="index,follow">
    <meta name="language" content="fa-IR">


    <link rel="stylesheet" href="{{ secure_asset('asset/css/bootstrap.min.css') }}"/>
    <link rel="stylesheet" href="{{ secure_asset('asset/css/swiper-bundle.min.css') }}"/>

    @vite(['resources/css/website.css'])

    <link rel="icon" href="{{ secure_asset('asset/img/logo.png') }}"/>

    @include('website.components.yektanet-ads', ['position' => 'head'])
