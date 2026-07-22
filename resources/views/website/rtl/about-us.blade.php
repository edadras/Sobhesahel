@extends('website.rtl.partials.base')

@section('style')
    <link rel="stylesheet" href="{{ asset('asset/css/archive-rtl.css') }}"/>
@endsection

@section('script')
    <script src="{{ asset('js/contact.js?ver=' . env('JS_ASSET_VER')) }}"></script>
@endsection



@section('content')
    <section class="aboutSec">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1>درباره ما</h1>
                    <div class="aboutPgBox text-end">
                       {!! $about !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
