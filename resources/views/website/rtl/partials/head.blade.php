<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    {{-- SEO: title, meta description, canonical, Open Graph, Twitter cards + JSON-LD structured data --}}
    @include('website.components.seo-meta')
    @include('website.components.seo-jsonld')

    <link rel="manifest" href="/manifest.json">


    <link rel="stylesheet" href="{{ secure_asset('asset/css/bootstrap.min.css') }}"/>
    <link rel="stylesheet" href="{{ secure_asset('asset/css/swiper-bundle.min.css') }}"/>

    @vite(['resources/css/website.css'])

    <link rel="icon" href="{{ secure_asset('asset/img/logo.png') }}"/>

    @include('website.components.yektanet-ads', ['position' => 'head'])
    {!! rescue(fn () => setting('scripts.head'), '', false) !!}
