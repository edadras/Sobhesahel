@extends('website.ltr.partials.base')

@section('style')
    <link rel="stylesheet" href="{{ asset('asset/css/archive-ltr.css') }}"/>
@endsection

@section('script')
    <script src="{{ asset('js/contact.js?ver=' . env('JS_ASSET_VER')) }}"></script>
@endsection

@section('content')
    <section class="aboutSec">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1>About us</h1>
                    <div class="aboutPgBox text-start">
                       {!! $about !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
