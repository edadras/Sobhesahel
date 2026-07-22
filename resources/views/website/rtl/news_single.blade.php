@extends('website.rtl.partials.base')

@section('style')
    <link rel="stylesheet" href="{{ asset('asset/css/news-rtl.css') }}"/>
{{--    <meta name="csrf-token" content="{{csrf_token()}}">--}}

    <style>
        .newsText img {
            max-width: 100%;
            height: auto;
            display: block;
        }
    </style>
@endsection

{{--@section('script')--}}
{{--    <script src="{{ asset('js/comments.js?ver=' . env('JS_ASSET_VER')) }}"></script>--}}
{{--    <script src="{{ asset('js/poll.js?ver=' . env('JS_ASSET_VER')) }}"></script>--}}
{{--    <script src="{{ asset('js/helper.js?ver=' . env('JS_ASSET_VER')) }}"></script>--}}
{{--@endsection--}}

@section('content')
    <section class="newsPgSec">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="pageRow">
                        <div class="pageMainBx">
                            <div class="newsTopImg position-relative">
                                <img src="{{ $post->getImageUrl() }}" alt="img">
                                <ul class="newsShare">
                                    <li>
                                        <a href="#">
                                            <span class="icon-Telegram-1"></span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            <span class="icon-Youtube-1"></span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            <span class="icon-Whatsapp"></span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            <span class="icon-Twitter-X-1"></span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            <span class="icon-Linkedin-1"></span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            <span class="icon-Vector-3011"></span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="newsDateRow">
                                @foreach($post->categories as $item)

                                    <a href="{{ route('website.rtl.category',['slug' => $item['slug']]) }}">
                                        <p>
                                            {{ $item['title'] }}
                                        </p>
                                    </a>

                                @endforeach

                                <div>

                                    <i>
                                        {{ $post['posted_at_jalali'] }}
                                    </i>
                                    <span class="icon-Vector-Stroke-4"></span>
                                    <i>
                                        {{ count($comments) }}
                                        کامنت</i>
                                </div>
                            </div>

                            @if(isset($post->author['id']) && $post->author['id'] != 78)
                                <div class="noteUsrRow">
                                    <div class="noteUsrBx">
                                        <div class="noteUsrImg">
                                            <img src="{{ $post->author['avatar']}}" alt="img">
                                        </div>
                                        <div class="text-end">
                                            <a href="{{ route('website.rtl.author',['user_type' => $post->author_id == null ? 'user' : 'author','id' => $post->author['id']]) }}">
                                                <strong>
                                                    {{ $post->author['name'] }}
                                                </strong>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="newsTitlBox text-end" id="pageHead">
                                <h6>
                                    {{ $post['sub_title'] }}
                                </h6>

                                <h1>
                                    {{ $post['title'] }}
                                </h1>

                                <p>
                                    {!! strip_tags($post['short_description']) !!}
                                </p>

                                <div>
                                    <p>اشتراک گذاری:</p>
                                    <ul class="hdrTopLnks">
                                        @php
                                            $postUrl = urlencode(url()->current());
                                            $postTitle = urlencode($post->title ?? ''); // Optional: If you want to include a title for platforms like Twitter and LinkedIn
                                        @endphp
                                        <li>
                                            <a href="https://telegram.me/share/url?url={{ $postUrl }}" target="_blank"
                                               class="transitionCls">
                                                <span class="icon-Telegram-1"></span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $postUrl }}"
                                               target="_blank" class="transitionCls">
                                                <span class="icon-Linkedin-1"></span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="https://twitter.com/intent/tweet?url={{ $postUrl }}&text={{ $postTitle }}"
                                               target="_blank" class="transitionCls">
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
                                        <li>
                                            {{--                                            @guest--}}
                                            {{--                                                <a href="{{ route('profile.login') }}" target="_blank"--}}
                                            {{--                                                   class="transitionCls">--}}
                                            {{--                                                    <small class="icon-Group-2344"></small>--}}
                                            {{--                                                </a>--}}
                                            {{--                                            @endguest--}}

                                            {{--                                            @auth--}}
                                            {{--                                                  @if($is_marked)--}}
                                            {{--                                                        <small @click="bookmark({{ $post['id'] }})" ref="bookmark_btn" class="icon-Group-2344 icon-active"></small>--}}
                                            {{--                                                    @else--}}
                                            {{--                                                        <small @click="bookmark({{ $post['id'] }})" ref="bookmark_btn" class="icon-Group-2344"></small>--}}
                                            {{--                                                      @endif--}}
                                            {{--                                            @endauth--}}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="newsText text-end">
                                {!! $post['body'] !!}
                            </div>

{{--                            @if(count($post['tags']) != 0)--}}
{{--                                <div class="newsTags">--}}
{{--                                    <p>تگ ها:</p>--}}
{{--                                    <ul>--}}
{{--                                        @foreach($post['tags'] as $tag)--}}
{{--                                            @if($tag['name'] != '')--}}
{{--                                                <a href="{{ route('website.rtl.tag',['name' => $tag['name']]) }}">--}}
{{--                                                    <li>--}}
{{--                                                        #{{$tag['name']}}--}}
{{--                                                    </li>--}}
{{--                                                </a>--}}
{{--                                            @endif--}}
{{--                                        @endforeach--}}
{{--                                    </ul>--}}
{{--                                </div>--}}
{{--                            @endif--}}

                            @livewire('news-comments', ['post' => $post])

                        </div>
                        @include('website.rtl.sidebar')
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection


