@extends('website.ltr.partials.base')

@section('style')
    <link rel="stylesheet" href="{{ asset('asset/css/archive-ltr.css') }}">
@endsection

@section('script')
    <script src="{{asset('js/helper.js?ver=' . env('JS_ASSET_VER'))}}"></script>
@endsection

@section('content')
    <div class="authorPgSec">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="authorPgRow">
                        <div class="right">
                            <div id="pageHead" class="authorInfo">
                                <div class="imgBx">
                                    <img src="{{ $user['avatar'] }}" alt="img">
                                </div>
                                <div class="name text-center">
                                    <h1>
                                        {{ $user['first_name'] . ' ' . $user['last_name'] }}
                                    </h1>
                                    <p>
                                        {{ $user['nik_name'] }}
                                    </p>
                                </div>
                                @guest
                                    <a href="{{ route('profile') }}" class="follow transitionCls">
                                        <span class="icon-Group-2330"></span>
                                        <i>دنبال کنید</i>
                                    </a>
                                @endguest

                                @auth
                                    @if($has_follow)
                                        <a @click.prevent="author({{$user['id']}})" class="follow transitionCls" ref="follow_btn">
                                            <span class="icon-Group-2330"></span>
                                            <i>دنبال میکنید</i>
                                        </a>
                                    @else
                                        <a @click.prevent="author({{$user['id']}})" class="follow transitionCls" ref="follow_btn">
                                            <span class="icon-Group-2330"></span>
                                            <i>دنبال کنید</i>
                                        </a>
                                    @endif
                                @endauth
                            </div>
                            <div class="authorBio text-center">
                                <strong>Biography</strong>
                                <p>
                                    {{ $user['bio'] }}
                                </p>
                            </div>
                            <div class="authorLink">
                                <strong class="text-center">ShortLink</strong>
                                <a href="{{ route('website.rtl.author',['id' => $user['id']]) }}">
                                    <p>
                                        {{ route('website.rtl.author',['id' => $user['id']]) }}
                                    </p>
                                    <span class="icon-Vector-3011"></span>
                                </a>
                            </div>
                        </div>
                        <div class="left">
                            <h2>Notes</h2>
                            @foreach($posts as $item)
                                <a href="{{ $item['url'] }}" class="authorNote transitionCls">
                                    <div class="position-relative">
                                        {{ $item['title'] }}
                                    </div>
                                    <p>
                                        <i>
                                            {{ $item['posted_at'] }}
                                        </i>
                                        <span class="icon-Group-2321"></span>
                                    </p>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

