<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title>{{ $website_title ?? \App\Models\AppSetting::get_setting('en_page_title') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta property="og:title" content="{{ $seo['title'] ?? 'Sobhe Sahel News Group' }}">
    <meta property="og:description" content="{{ $seo['description'] ?? 'Sobhe Sahel News Group' }}">
    <meta property="og:type" content="{{ $seo['type'] ?? 'article' }}">
    <meta property="og:site_name" content="Sobhe Sahel News Group">
    <meta property="og:locale" content="en_IR">
    <meta property="og:url" content="{{ request()->fullUrl() }}">
    @if(isset($seo['image']))
        <meta property="og:image" content="{{ $seo['image'] }}">
@endif

<!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@sobhesahel">
    <meta name="twitter:title" content="{{ $seo['title'] ?? 'Sobhe Sahel News Group' }}">
    <meta name="twitter:description" content="{{ $seo['description'] ?? 'Sobhe Sahel News Group' }}">
    <meta name="twitter:domain" content="sobhesahel.com">
    <meta name="twitter:creator" content="@sobhesahel">

    <!-- SEO Meta -->
    <meta name="description" content="{{ $seo['description'] ?? 'Sobhe Sahel News Group' }}">
    <meta name="keywords" content="{{ $seo['keywords'] ?? '' }}">
    <meta name="robot" content="index,follow">
    <meta name="language" content="en">


    <link rel="stylesheet" href="{{ asset('asset/css/bootstrap.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('asset/css/animate.css') }}"/>
    <link rel="stylesheet" href="{{ asset('asset/css/main-ltr.css') }}"/>
    <link rel="stylesheet" href="{{ asset('asset/css/icomoon.css') }}"/>
    <link rel="stylesheet" href="{{ asset('asset/css/swiper-bundle.min.css') }}"/>

    <link rel="stylesheet" href="{{ asset('asset/css/audioplayer.css') }}"/>
    <link rel="stylesheet" href="{{ asset('asset/css/lg-video.css') }}"/>
    <link rel="stylesheet" href="{{ asset('asset/css/video-js.css') }}"/>

    <link rel="icon" href="{{ asset('asset/img/logo.png') }}"/>

    @include('website.components.yektanet-ads', ['position' => 'head'])
