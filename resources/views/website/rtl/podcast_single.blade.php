@extends('website.rtl.partials.base')


@section('style')
    <link rel="stylesheet" href="{{ asset('asset/css/news-rtl.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/css/audioplayer.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/css/lg-video.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/css/video-js.css') }}">
@endsection

@section('script')
    <script src="{{ asset('asset/js/audioplayer.js') }}"></script>
    <script src="{{ asset('asset/js/lg-video.min.js') }}"></script>
    <script src="{{ asset('asset/js/video.js') }}"></script>
    <script src="{{ asset('js/helper.js?ver=' . env('JS_ASSET_VER')) }}"></script>
@endsection

@section('content')
    <section class="newsPgSec">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="pageRow">
                        <div class="pageMainBx">
                            <div class="podPgTop">
                                <div class="topPodImg">
                                    <img src="{{ $post->getImageUrl() }}" alt="img">
                                </div>
                                <div class="topPodInfo" id="pageHead">
                                    <h1>
                                        {{ $post['title'] }}
                                    </h1>
                                    <div class="share">
                                        <p>اشتراک گذاری:</p>
                                        <ul class="hdrTopLnks">
                                            @php
                                                $postUrl = urlencode(url()->current());
                                                $postTitle = urlencode($post->title ?? ''); // Optional: If you want to include a title for platforms like Twitter and LinkedIn
                                            @endphp
                                            <li>
                                                <a href="https://telegram.me/share/url?url={{ $postUrl }}"
                                                   target="_blank" class="transitionCls">
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
                                                <a href="https://www.youtube.com/" target="_blank"
                                                   class="transitionCls">
                                                    <span class="icon-Youtube-1"></span>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="https://www.instagram.com/" target="_blank"
                                                   class="transitionCls">
                                                    <span class="icon-Instagram-1"></span>
                                                </a>
                                            </li>
                                            <li>
                                                <i class="icon-Vector-Stroke-4"></i>
                                            </li>
                                            <li>
{{--                                                @guest--}}
{{--                                                    <a href="{{ route('profile.login') }}" target="_blank"--}}
{{--                                                       class="transitionCls">--}}
{{--                                                        <small class="icon-Group-2344"></small>--}}
{{--                                                    </a>--}}
{{--                                                @endguest--}}

{{--                                                @auth--}}
{{--                                                    @if($is_marked)--}}
{{--                                                        <small @click="bookmark({{ $post['id'] }})" ref="bookmark_btn" class="icon-Group-2344 icon-active"></small>--}}
{{--                                                    @else--}}
{{--                                                        <small @click="bookmark({{ $post['id'] }})" ref="bookmark_btn" class="icon-Group-2344"></small>--}}
{{--                                                    @endif--}}
{{--                                                @endauth--}}
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="date">
                                        {{ $post['posted_at_jalali'] }}
                                    </div>
                                </div>
                                <div class="pdcstAudio pdcstAudio2 position-relative">
                                    <div class="app-cover">
                                        <div class="player">
                                            <div class="audioList">
                                                <audio src="{{ \Illuminate\Support\Facades\Storage::url($post['attachments'][0]) }}"></audio>
                                            </div>
                                            <div class="player-track">
                                                <div id="track-time">
                                                    <div id="track-length">00:00</div>
                                                    <div id="current-time">00:00</div>
                                                </div>
                                                <div id="s-area">
                                                    <div id="ins-time" style="left: 0px; margin-left: 0px;">00:00</div>
                                                    <div id="s-hover" style="width: 0px;"></div>
                                                    <div id="seek-bar" style="width: 0px;"></div>
                                                </div>
                                            </div>
                                            <div class="player-content">
                                                <div class="player-controls">
                                                    <div class="control">
                                                        <div class="button transitionCls play-next">
                                                            <i class="icon-Play-7-1 transitionCls"></i>
                                                        </div>
                                                    </div>
                                                    <div class="control">
                                                        <div class="button transitionCls play-pause-button">
                                                            <i class="icon-play-circle-rounded transitionCls"></i>
                                                        </div>
                                                    </div>
                                                    <div class="control">
                                                        <div class="button transitionCls play-previous">
                                                            <i class="icon-Play-8-1 transitionCls"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="topPodTxt text-end">
                                    <strong class="position-relative d-block">
                                        درباره این پادکست
                                    </strong>
                                    {!! $post['body'] !!}
                                </div>
                            </div>


                            <div class="podEpisodes">
                                <div class="title">اپیزودها</div>
                                <div class="audioList">
                                    @foreach($episodes as $item)
                                        <div class="episodCard transitionCls">
                                            <img src="{{ asset($item['image_large']) }}" class="episodImg"
                                                 alt="img">
                                            <div class="episdCrdInfo">
                                                <div class="text-end">
                                                    <h2>
                                                        {{ $item['title'] }}
                                                    </h2>
                                                    {{--                                                    <p>قسمت شانزدهم</p>--}}
                                                    <ul>
                                                        <li>
                                                            <span class="icon-Vector-827"></span>
                                                            <i>
                                                                {{ $item['duration'] ?? 0 }}
                                                            </i>
                                                        </li>
                                                        <li>
                                                            <span class="icon-Group-2321"></span>
                                                            <i>
                                                                {{ $item['posted_at_jalali'] }}
                                                            </i>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <audio src="{{ \Illuminate\Support\Facades\Storage::url($item['attachments'][0]) }}"
                                                       class="one"></audio>
                                                <div class="playIconBtn">
                                                    <i class="icon-play-circle-rounded transitionCls"></i>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @include('website.rtl.sidebar')
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
