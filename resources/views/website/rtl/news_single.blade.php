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

                            @foreach(['image_second', 'image_third'] as $extraImageField)
                                @php $extraImageUrl = $post->getFileUrl($post->{$extraImageField} ?? null); @endphp
                                @if($extraImageUrl)
                                    <div class="newsTopImg position-relative" style="margin-top: 10px">
                                        <img src="{{ $extraImageUrl }}" alt="{{ $post->title }}">
                                    </div>
                                @endif
                            @endforeach

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
                                    @if($post->show_visits ?? true)
                                        <span class="icon-Vector-Stroke-4"></span>
                                        <i>
                                            {{ $post->visits ?? 0 }}
                                            بازدید</i>
                                    @endif
                                    @if($post->show_comments ?? true)
                                        <span class="icon-Vector-Stroke-4"></span>
                                        <i>
                                            {{ count($comments) }}
                                            کامنت</i>
                                    @endif
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

                                <h1 @if(!empty($post->title_color)) style="color: {{ $post->title_color }}" @endif>
                                    {{ $post['title'] }}
                                </h1>

                                <p>
                                    {!! strip_tags($post['short_description']) !!}
                                </p>

                                @php $subtitles = array_filter(is_array($post->subtitles ?? null) ? $post->subtitles : []); @endphp
                                @if(count($subtitles))
                                    <ul class="newsSubtitles text-end" style="list-style: none; padding: 0; margin: 10px 0">
                                        @foreach($subtitles as $subtitle)
                                            <li style="margin-bottom: 5px">
                                                <strong>{{ is_array($subtitle) ? ($subtitle['subtitle'] ?? reset($subtitle)) : $subtitle }}</strong>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

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
                            @php
                                $mediaUrl = method_exists($post, 'getMediaFileUrl') ? $post->getMediaFileUrl() : null;
                                $mediaExt = $mediaUrl ? strtolower(pathinfo(parse_url($mediaUrl, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION)) : null;
                            @endphp
                            @if($mediaUrl)
                                @if(in_array($mediaExt, ['mp4', 'webm', 'mov', 'm4v']))
                                    <div class="videoPgRight position-relative" style="margin: 15px 0">
                                        <video controls preload="metadata" style="width: 100%; max-width: 100%" src="{{ $mediaUrl }}">
                                            مرورگر شما از پخش ویدئو پشتیبانی نمی‌کند.
                                        </video>
                                    </div>
                                @else
                                    <div class="pdcstAudio pdcstAudio2 position-relative" style="margin: 15px 0">
                                        <audio controls preload="metadata" style="width: 100%" src="{{ $mediaUrl }}">
                                            مرورگر شما از پخش صوت پشتیبانی نمی‌کند.
                                        </audio>
                                    </div>
                                @endif
                            @endif

                            @if(!empty($post->pre_message))
                                <div class="newsText text-end newsPreMessage">
                                    <p><strong>{{ $post->pre_message }}</strong></p>
                                </div>
                            @endif

                            <div class="newsText text-end">
                                {!! $post['body'] !!}
                            </div>

                            @if(!empty($post->post_message))
                                <div class="newsText text-end newsPostMessage">
                                    <p><strong>{{ $post->post_message }}</strong></p>
                                </div>
                            @endif

                            @if(!empty($post->id ?? null))
                                @livewire('content-rating', ['post' => $post])
                            @endif

                            @include('website.components.advertise-banner', ['position' => 'single_page_advertise_image'])

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

                            @if($post->show_comments ?? true)
                                @livewire('news-comments', ['post' => $post])
                            @endif

                        </div>
                        @include('website.rtl.sidebar')
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection


