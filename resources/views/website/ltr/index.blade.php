@extends('website.ltr.partials.base')

@section('content')
    <section class="topSec">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="topSecBx">
                        <div class="topSecRght">
                            <div class="swiper">
                                <div class="swiper-wrapper">
                                    @foreach($pdf as $item)
                                        <a href="{{ route('website.rtl.archive.pdf',['number' => $item['archive_number'],'archive' => $item['archive_category']]) }}"
                                           class="swiper-slide transitionCls">
                                            <img src="{{ asset($item['image_large']) }}" alt="img"/>
                                        </a>
                                    @endforeach
                                </div>
                                <div class="topRghtNav">
                                    <div class="swiper-button-prev"></div>
                                    <div>
                                        <p>SobheSahel News Paper</p>
                                    </div>
                                    <div class="swiper-button-next"></div>
                                </div>
                            </div>
                        </div>

                        <div class="topSecLft">
                            <div class="topLftSldr">
                                <div class="swiper">
                                    <div class="swiper-wrapper">
                                        @foreach($latest->take(3) as $item)
                                            <a href="{{ $item['url'] }}"
                                               class="swiper-slide transitionCls">
                                                <div class="topLftImg">
                                                    <img src="{{ asset($item['image_large']) }}"
                                                         alt="img"/>
                                                </div>
                                                <div class="topLftInfo">
                                                    <strong>
                                                        {{ $item['title'] }}
                                                    </strong>
                                                    {!! $item['short_description'] !!}
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                    <div class="topleftNav">
                                        <div class="topLftPgSc">
                                            <div class="swiper-pagination"></div>
                                            <div class="swiper-scrollbar"></div>
                                        </div>
                                        <div class="topLftBtns">
                                            <div class="swiper-button-prev"></div>
                                            <div class="swiper-button-next"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="topNewsRow">
                                @foreach($latest->skip(3)->take(3) as $item)
                                    <div class="topNewsBx position-relative">
                                        <img
                                            src="{{ asset($item['image_large']) }}"
                                            class="position-relative"
                                            alt="img"
                                        />
                                        <div class="topNewsCvr transitionCls">
                                            <a href="{{ $item['url'] }}">
                                                <p>
                                                    {{ $item['title'] }}
                                                </p>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if($setting['top_row']['is_active'])
        <section class="catOneSec">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="catOneRow">
                            <div class="catOneBox articlsCat">
                                <a href="#" class="head">سر مقاله ها</a>
                                <div class="body">
                                    <div class="swiper">
                                        <div class="swiper-wrapper">
                                            @foreach($notes->take(3) as $item)
                                                <a href="{{ $item['url'] }}" class="swiper-slide">
                                                    <div class="artclAuthor">
                                                        <i><img src="{{ asset($item['author']['avatar']) }}"
                                                                alt="user"/></i>
                                                        <div class="text-start">
                                                            <p>{{$item['author']['first_name'] . ' ' . $item['author']['last_name']}}</p>
                                                            <span>{{ $item['author']['nik_name'] }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="artclText">
                                                        <h2>
                                                            {{ $item['title'] }}
                                                        </h2>
                                                        {!! $item['short_description'] !!}
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                        <div class="swiper-pagination"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="catOneBox notesCat">
                                <a href="#" class="head">یادداشت ها</a>
                                <div class="body">
                                    <ul class="notesList">
                                        @foreach($notes->skip(3)->take(8) as $item)
                                            <li>
                                                <a href="{{ $item['url'] }}">
                                                    <i>
                                                        <img src="{{ $item['author']['avatar'] }}" alt="user"/>
                                                    </i>
                                                    <div class="text-start">
                                                        <p>
                                                            {{ $item['title'] }}
                                                        </p>
                                                        <span>{{$item['author']['first_name'] . ' ' . $item['author']['last_name']}}</span>
                                                    </div>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            @foreach($podcast as $item)
                                <div class="catOneBox podcastCat">
                                    <div class="podRight">
                                        <a href="#" class="head">پادکست</a>
                                        <div class="body">
                                            <div class="podcstUser">
                                                <img src="{{ asset($item['image_large']) }}" alt="img"/>
                                                <div class="text-start">
                                                    <p>
                                                        {{ $item['title'] }}
                                                    </p>
                                                    {{--                                                    <span>قسمت اول</span>--}}
                                                </div>
                                            </div>
                                            <div class="pdcstAudio position-relative">
                                                <div class="audioList">
                                                    <audio src="{{ asset('asset/songs/2.mp3') }}"></audio>
                                                    <audio src="{{ asset('asset/songs/2.mp3') }}"></audio>
                                                    <audio src="{{ asset('asset/songs/2.mp3') }}"></audio>
                                                </div>
                                                <div class="app-cover">
                                                    <div class="player">
                                                        <div class="player-track">
                                                            <div id="track-time">
                                                                <div id="track-length"></div>
                                                                <div id="current-time"></div>
                                                            </div>
                                                            <div id="s-area">
                                                                <div id="ins-time"></div>
                                                                <div id="s-hover"></div>
                                                                <div id="seek-bar"></div>
                                                            </div>
                                                        </div>
                                                        <div class="player-content">
                                                            <div class="player-controls">
                                                                <div class="control">
                                                                    <div class="button transitionCls play-previous">
                                                                        <i class="icon-Play-7-1 transitionCls"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="control">
                                                                    <div
                                                                        class="button transitionCls play-pause-button"
                                                                    >
                                                                        <i
                                                                            class="icon-play-circle-rounded transitionCls"
                                                                        ></i>
                                                                    </div>
                                                                </div>
                                                                <div class="control">
                                                                    <div class="button transitionCls play-next">
                                                                        <i class="icon-Play-8-1 transitionCls"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pdcstCatImg">
                                        <img src="{{ asset('asset/img/img04.jpg') }}" alt="img"/>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if($setting['rows']['row_1']['is_active'])
        <section class="catTwoSec">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="catTwoRow">
                            @foreach($setting['rows']['row_1']['columns'] as $col_key => $col)
                                <div class="catTwoBox">
                                    <div class="head homPgTitle">
                                        <span></span>
                                        <p>{{ $col['title'] }}</p>
                                    </div>
                                    @foreach($posts[$col_key]->take(1) as $post)
                                        <a href="{{ $post['url'] }}"
                                           class="top position-relative transitionCls">
                                            <img
                                                src="{{ asset($post['image_large']) }}"
                                                class="position-relative"
                                                alt="img"
                                            />
                                            <div>
                                                <h2>{{ $post['title'] }}</h2>
                                                <p>{{ $post['posted_at'] }}</p>
                                            </div>
                                        </a>
                                    @endforeach
                                    <div class="body">
                                        @foreach($posts[$col_key]->skip(1)->take(3) as $item)
                                            <a href="{{ $item['url'] }}"
                                               class="transitionCls">
                                                <img src="{{ asset($item['image_large']) }}" alt="img"/>
                                                <div class="text-start pt-1">
                                                    <span>{{ $item['posted_at'] }}</span>
                                                    <p>
                                                        {{ $item['title'] }}
                                                    </p>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if($setting['video']['is_active'])
        <section class="mediaSec position-relative mb-5">
            <div class="mediaSecBg position-absolute"></div>
            <div class="container position-relative">
                <div class="row">
                    <div class="col-12">
                        <div class="homPgTitle">
                            <span></span>
                            <strong>Videos</strong>
                        </div>
                        <div class="videoSecBx">
                            <div class="videoList">
                                @foreach($video->skip(1)->take(5) as $item)
                                    <a href="{{ $item['url'] }}"
                                       class="transitionCls">
                                        <div class="position-relative">
                                            <img src="{{ asset($item['image_large']) }}" alt="img"/>
                                            <p class="position-absolute">
                                                {{--                                            <small> 15:20 </small>--}}
                                                <span class="icon-Player---Play---SVG-1"></span>
                                            </p>
                                        </div>
                                        <i>
                                            {{ $item['title'] }}
                                        </i>
                                    </a>
                                @endforeach
                            </div>
                            @foreach($video->take(1) as $item)
                                <div class="mainVideo position-relative">
                                    <video
                                        id="my-player-1"
                                        class="video-js position-relative"
                                        controls="true"
                                        preload="auto"
                                        poster="{{ asset($item['image_large']) }}"
                                        width="100%"
                                        height="100%"
                                        data-setup='{"fluid": false}'
                                    >
                                        <source src="{{ asset($item['video']['video']) }}"
                                                type="video/mp4"/>
                                        Your browser does not support the video tag.
                                    </video>
                                    <div class="vidOverlay position-absolute">
                                        <strong>
                                            {{ $item['title'] }}
                                        </strong>
                                        {!! $item['short_description'] !!}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if($setting['rows']['row_2']['is_active'])
        <section class="catTwoSec">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="catTwoRow">
                            @foreach($setting['rows']['row_2']['columns'] as $col_key => $col)
                                <div class="catTwoBox">
                                    <div class="head homPgTitle">
                                        <span></span>
                                        <p>{{ $col['title'] }}</p>
                                    </div>
                                    @foreach($posts[$col_key]->take(1) as $post)
                                        <a href="{{ $post['url'] }}"
                                           class="top position-relative transitionCls">
                                            <img
                                                src="{{ asset($post['image_large']) }}"
                                                class="position-relative"
                                                alt="img"
                                            />
                                            <div>
                                                <h2>{{ $post['title'] }}</h2>
                                                <p>{{ $post['posted_at'] }}</p>
                                            </div>
                                        </a>
                                    @endforeach
                                    <div class="body">
                                        @foreach($posts[$col_key]->skip(1)->take(3) as $item)
                                            <a href="{{ $item['url'] }}"
                                               class="transitionCls">
                                                <img src="{{ asset($item['image_large']) }}" alt="img"/>
                                                <div class="text-start pt-1">
                                                    <span>{{ $item['posted_at'] }}</span>
                                                    <p>
                                                        {{ $item['title'] }}
                                                    </p>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if($setting['photos']['is_active'])
        <section class="mediaSec position-relative">
            <div class="mediaSecBg position-absolute"></div>
            <div class="container position-relative">
                <div class="row">
                    <div class="col-12">
                        <div class="homPgTitle">
                            <span></span>
                            <strong>Gallery</strong>
                        </div>
                        <div class="imagSecBx">
                            <div class="top">
                                @foreach($photos->take(2) as $item)
                                    <a href="{{ $item['url'] }}"
                                       class="imageBx position-relative transitionCls">
                                        <img
                                            src="{{ asset($item['image_large']) }}"
                                            class="position-relative"
                                            alt="img"
                                        />
                                        <div class="position-absolute">
                                            <strong>
                                                {{ $item['title'] }}
                                            </strong>
                                            {!! $item['short_description'] !!}
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                            <div class="bottom">
                                @foreach($photos->skip(2)->take(3) as $item)
                                    <a href="{{ $item['url'] }}"
                                       class="imgCard transitionCls">
                                        <img src="{{ asset($item['image_large']) }}" alt="img"/>
                                        <div class="body">
                                        <span>
                                            {{ $item['posted_at_ago'] }}
                                        </span>
                                            <p>
                                                {{ $item['title'] }}
                                            </p>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if($setting['rows']['row_3']['is_active'])
        <section class="gulfNewsSec">
            <div class="container">
                <div class="row">
                    @foreach($setting['rows']['row_3']['columns'] as $col_key => $col)
                        <div class="col-12">
                            <div class="homPgTitle">
                                <span></span>
                                <strong>{{ $col['title'] }}</strong>
                            </div>
                            <div class="gulfNewsBox">
                                @foreach($posts[$col_key]->take(1) as $item)
                                    <a href="{{ $item['url'] }}"
                                       class="right position-relative transitionCls">
                                        <img
                                            src="{{ asset($item['image_large']) }}"
                                            class="position-relative"
                                            alt="img"
                                        />
                                        <div class="position-absolute">
                                            <strong>
                                                {{ $item['title'] }}
                                            </strong>
                                            {!! $item['short_description'] !!}
                                        </div>
                                    </a>
                                @endforeach
                                <div class="left">
                                    @foreach($posts[$col_key]->skip(1)->take(3) as $item)
                                        <a href="{{ $item['url'] }}"
                                           class="transitionCls">
                                            <img src="{{ asset('asset/img/news03.jpg') }}" alt="img"/>
                                            <div>
                                                <strong>
                                                    {{ $item['title'] }}
                                                </strong>
                                                {!! $item['short_description'] !!}
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif


    {{--    <div class="floutNews">--}}
    {{--        <div>--}}
    {{--            <i></i>--}}
    {{--            <strong>خـبر فوری:</strong>--}}
    {{--        </div>--}}
    {{--        <p>--}}
    {{--            سیدحسن نصرالله: ایران از محور مقاومت حمایت می‌کند ولی در طوفان‌الاقصی--}}
    {{--            دخالتی نداشت.--}}
    {{--        </p>--}}
    {{--        <span class="icon-Group-2168 clsFloutNws"></span>--}}
    {{--    </div>--}}
@endsection
