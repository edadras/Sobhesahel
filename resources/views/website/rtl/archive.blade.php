@extends('website.rtl.partials.base')

@section('style')
    <link rel="stylesheet" href="{{ asset('asset/css/archive-rtl.css') }}"/>
    <link rel="stylesheet" href="{{ asset('asset/css/persian-datepicker.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('asset/css/audioplayer.css') }}"/>
    <link rel="stylesheet" href="{{ asset('asset/css/lg-video.css') }}"/>
    <link rel="stylesheet" href="{{ asset('asset/css/video-js.css') }}"/>
@endsection
@section('script')
    <script src="{{ asset('js/archive.js?ver=' . env('JS_ASSET_VER')) }}"></script>
    <script src="{{asset('asset/js/tabs.js')}}"></script>
    <script src="{{asset('asset/js/persian-date.min.js')}}"></script>
    <script src="{{asset('asset/js/persian-datepicker.min.js')}}"></script>
    <script src="{{asset('asset/js/lg-video.min.js')}}"></script>
    <script src="{{asset('asset/js/video.js')}}"></script>
@endsection


@section('content')
    @livewire('archive-index')
@endsection
