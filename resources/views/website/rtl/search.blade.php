@extends('website.rtl.partials.base')

@section('style')
    <link rel="stylesheet" href="{{ asset('asset/css/persian-datepicker.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('asset/css/archive-rtl.css') }}"/>
@endsection

@section('script')
{{--    <script src="{{ asset('js/search.js?ver=' . env('JS_ASSET_VER')) }}"></script>--}}
<script src="{{asset('asset/js/persian-date.min.js')}}"></script>
<script src="{{asset('asset/js/persian-datepicker.min.js')}}"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Then load Persian Datepicker -->
<script src="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>
@endsection

@section('content')
    <section class="archiveSec">
        <div class="container" id="app">
            <div class="row">
                <div class="col-12">
                    @livewire('search-index')
                </div>
            </div>
        </div>
    </section>
@endsection
