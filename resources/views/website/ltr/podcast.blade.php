@extends('website.ltr.partials.base')

@section('style')
    <link rel="stylesheet" href="{{ asset('asset/css/image-ltr.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/css/audioplayer.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/css/lg-video.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/css/video-js.css') }}">
@endsection

@section('script')
    <script src="{{ asset('asset/js/audioplayer.js') }}"></script>
    <script src="{{ asset('asset/js/lg-video.min.js') }}"></script>
    <script src="{{ asset('asset/js/video.js') }}"></script>
@endsection


@section('content')
    <section class="podPgSec">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    @foreach($posts->take(1) as $item)
                        <div class="podPgRow">
                            <div class="podPgRght">
                                <img src="{{ asset($item['image_large']) }}" alt="img">
                                <div class="podRowInfo text-start">
                                    <span>New arrived</span>
                                    <h2 class="position-relative">
                                        {{$item['title']}}
                                    </h2>
                                    {!! $item['post_body'] !!}
                                </div>
                            </div>
                            <div class="podPgLft">
                                <div class="pdcstAudio position-relative">
                                    <div class="audioList">
                                        <audio src="{{ asset($item['podcast']['podcast']) }}"></audio>
                                    </div>
                                    <div class="app-cover">
                                        <div class="player">
                                            <div class="player-content">
                                                <div class="player-controls">
                                                    <div class="control">
                                                        <div class="button transitionCls play-pause-button">
                                                            <p></p>
                                                            <span></span>
                                                            <small></small>
                                                            <i class="icon-play-circle-rounded transitionCls"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="player-track">
                                                <div id="track-time">
                                                    <div id="track-length">00:00</div>
                                                    <div id="current-time">00:00</div>
                                                </div>
                                                <div id="s-area">
                                                    <div id="ins-time"></div>
                                                    <div id="s-hover"></div>
                                                    <div id="seek-bar" style="width: 0px;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="recentPicSec">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="rcntPicHed d-flex align-items-center">
                        <div class="d-flex align-items-center justify-content-start">
                            <span class="d-block"></span>
                            <p>Latest episodes</p>
                        </div>
                        {{--                        <a href="#" class="d-flex align-items-center justify-content-end">--}}
                        {{--                            <i class="transitionCls">دیدن همه</i>--}}
                        {{--                            <span class="icon-Group-2210"></span>--}}
                        {{--                        </a>--}}
                    </div>
                    <div class="rcntPicBdy">
                        @foreach($posts->skip(1)->take(12) as $item)
                            <a href="{{ $item['url'] }}" class="rcntPicCrd">
                                <div class="rcntPicTop position-relative">
                                    <img src="{{ asset($item['image_large']) }}"
                                         class="position-relative transitionCls" alt="img">
                                    <div class="position-absolute">
                                        <span class="icon-Group-2162"></span>
                                    </div>
                                </div>
                                <div class="rcntPicTxt text-start">
                                    <h2>
                                        {{ $item['title'] }}
                                    </h2>
                                    {!! $item['post_body'] !!}
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
