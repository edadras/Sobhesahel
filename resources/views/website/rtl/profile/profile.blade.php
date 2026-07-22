@extends('website.rtl.partials.base')

@section('style')
    <link rel="stylesheet" href="{{ asset('asset/css/profile-rtl.css') }}">
@endsection

@section('content')
    <section class="profileSec" id="profile">
        <base-component></base-component>
    </section>

@endsection

@section('script')
    <script src="{{ asset('js/profile.js?ver=' . env('JS_ASSET_VER')) }}"></script>
@endsection

