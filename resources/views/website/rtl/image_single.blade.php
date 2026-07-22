@extends('website.rtl.partials.base')

@section('style')
    <link rel="stylesheet" href="{{ asset('asset/css/image-rtl.css') }}"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/css/lightgallery.min.css">
@endsection
@section('script')
    <script src="{{asset('asset/js/lightgallery.min.js')}}"></script>
    <script src="{{asset('js/helper.js?ver=' . env('JS_ASSET_VER'))}}"></script>
@endsection

@section('content')
    <section class="gallerySec">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="galleryHed" id="pageHead">
                        <div class="title text-end">
                            <h1>
                                {{ $post['title'] }}
                            </h1>
                            {!! $post['short_description'] !!}
                            {!! $post['post_body'] !!}
                        </div>
                        <div class="share">
                            <div>
                                <p>اشتراک گذاری:</p>
                                <ul class="hdrTopLnks">
                                    @php
                                        $postUrl = urlencode(url()->current());
                                        $postTitle = urlencode($post->title ?? ''); // Optional: If you want to include a title for platforms like Twitter and LinkedIn
                                    @endphp
                                    <li>
                                        <a href="https://telegram.me/share/url?url={{ $postUrl }}" target="_blank" class="transitionCls">
                                            <span class="icon-Telegram-1"></span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $postUrl }}" target="_blank" class="transitionCls">
                                            <span class="icon-Linkedin-1"></span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://twitter.com/intent/tweet?url={{ $postUrl }}&text={{ $postTitle }}" target="_blank" class="transitionCls">
                                            <span class="icon-Twitter-X-1"></span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://www.youtube.com/" target="_blank" class="transitionCls">
                                            <span class="icon-Youtube-1"></span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://www.instagram.com/" target="_blank" class="transitionCls">
                                            <span class="icon-Instagram-1"></span>
                                        </a>
                                    </li>
                                    <li>
                                        <i class="icon-Vector-Stroke-4"></i>
                                    </li>
{{--                                    <li>--}}
{{--                                        @guest--}}
{{--                                            <a href="{{ route('profile.login') }}" target="_blank"--}}
{{--                                               class="transitionCls">--}}
{{--                                                <small class="icon-Group-2344"></small>--}}
{{--                                            </a>--}}
{{--                                        @endguest--}}

{{--                                        @auth--}}
{{--                                            @if($is_marked)--}}
{{--                                                <small @click="bookmark({{ $post['id'] }})" ref="bookmark_btn" class="icon-Group-2344 icon-active"></small>--}}
{{--                                            @else--}}
{{--                                                <small @click="bookmark({{ $post['id'] }})" ref="bookmark_btn" class="icon-Group-2344"></small>--}}
{{--                                            @endif--}}
{{--                                        @endauth--}}
{{--                                    </li>--}}
                                </ul>
                            </div>
                            <p class="date">
                                {{ $post['posted_at_jalali'] }}
                            </p>
                        </div>
                    </div>
                    <div class="gallryBox">
                        <div id="lightgallery" class="lightgallery">
                            @foreach($post->attachments as $item)
                                <a href="{{ Illuminate\Support\Facades\Storage::url($item) }}" class="glryCard transitionCls" data-lg-id="{{ \Illuminate\Support\Str::random(10) }}">
                                    <img src="{{ Illuminate\Support\Facades\Storage::url($item) }}" alt="{{ $post['title'] }}">
                                </a>
                            @endforeach
                        </div>
                    </div>

                    @include('website.components.advertise-banner', ['position' => 'single_page_advertise_image'])
                </div>
            </div>
        </div>
    </section>
@endsection
