@extends('website.ltr.partials.base')

@section('style')
    <link rel="stylesheet" href="{{ asset('asset/css/image-ltr.css') }}"/>
    <link rel="stylesheet" href="{{ asset('asset/css/audioplayer.css') }}"/>
    <link rel="stylesheet" href="{{ asset('asset/css/lg-video.css') }}"/>
    <link rel="stylesheet" href="{{ asset('asset/css/video-js.css') }}"/>
@endsection
@section('script')
    <script src="{{asset('asset/js/lg-video.min.js')}}"></script>
    <script src="{{asset('asset/js/video.js')}}"></script>
    <script src="{{asset('js/helper.js?ver=' . env('JS_ASSET_VER'))}}"></script>
@endsection

@section('content')
    <section class="videoPgSec" style="margin-bottom: 100px">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="videoPgRow">
                        <div class="videoPgRight position-relative">
                            <video
                                id="my-player-1"
                                class="video-js position-relative"
                                controls="true"
                                preload="auto"
                                poster="{{ asset($post['image_large']) }}"
                                width="100%"
                                height="100%"
                                data-setup='{"fluid": false}'
                            >
                                <source src="{{ asset($video['video']) }}" type="video/mp4"/>
                                Your browser does not support the video tag.
                            </video>
                            <div class="vidOverlay position-absolute">
{{--                                <p>00:18</p>--}}
                                <span></span>
                            </div>
                        </div>
                        <div class="videoPgLeft text-start" id="pageHead">
                            <div class="text">
                                <h2>
                                    {{ $post['title'] }}
                                </h2>
                                <span>
                                    {{ $post['posted_at'] }}
                                </span>
                                {!! $post['short_description'] !!}
                                {!! $post['post_body'] !!}
                            </div>
                            <div class="share">
                                <p>Share:</p>
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
                                    <li>
                                        @guest
                                            <a href="{{ route('profile') }}" target="_blank"
                                               class="transitionCls">
                                                <small class="icon-Group-2344"></small>
                                            </a>
                                        @endguest

                                        @auth
                                            @if($is_marked)
                                                <small @click="bookmark({{ $post['id'] }})" ref="bookmark_btn" class="icon-Group-2344 icon-active"></small>
                                            @else
                                                <small @click="bookmark({{ $post['id'] }})" ref="bookmark_btn" class="icon-Group-2344"></small>
                                            @endif
                                        @endauth
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

{{--    <section class="imgCatSec">--}}
{{--        <div class="container">--}}
{{--            <div class="row">--}}
{{--                <div class="col12">--}}
{{--                    <div class="imgCatBdy">--}}
{{--                        <a href="#" class="imgCatCrd transitionCls">--}}
{{--                            <div class="imgCatTop position-relative">--}}
{{--                                <img--}}
{{--                                    src="../asset/img/img02.jpg"--}}
{{--                                    class="position-relative"--}}
{{--                                    alt="img"--}}
{{--                                />--}}
{{--                                <div class="position-absolute">--}}
{{--                                    <i>00:15</i><span class="icon-play-arrow-rounded"></span>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="imgCatTxt text-end">--}}
{{--                                <span>فرهنگ و ادب</span>--}}
{{--                                <h2>نخستین همایش ملی فرماندهی و مدیریت در جنگ‌های آینده</h2>--}}
{{--                            </div>--}}
{{--                        </a>--}}
{{--                        <a href="#" class="imgCatCrd transitionCls">--}}
{{--                            <div class="imgCatTop position-relative">--}}
{{--                                <img--}}
{{--                                    src="../asset/img/img07.jpg"--}}
{{--                                    class="position-relative"--}}
{{--                                    alt="img"--}}
{{--                                />--}}
{{--                                <div class="position-absolute">--}}
{{--                                    <i>00:15</i><span class="icon-play-arrow-rounded"></span>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="imgCatTxt text-end">--}}
{{--                                <span>فرهنگ و ادب</span>--}}
{{--                                <h2>نخستین همایش ملی فرماندهی و مدیریت در جنگ‌های آینده</h2>--}}
{{--                            </div>--}}
{{--                        </a>--}}
{{--                        <a href="#" class="imgCatCrd transitionCls">--}}
{{--                            <div class="imgCatTop position-relative">--}}
{{--                                <img--}}
{{--                                    src="../asset/img/img08.jpg"--}}
{{--                                    class="position-relative"--}}
{{--                                    alt="img"--}}
{{--                                />--}}
{{--                                <div class="position-absolute">--}}
{{--                                    <i>00:15</i><span class="icon-play-arrow-rounded"></span>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="imgCatTxt text-end">--}}
{{--                                <span>فرهنگ و ادب</span>--}}
{{--                                <h2>نخستین همایش ملی فرماندهی و مدیریت در جنگ‌های آینده</h2>--}}
{{--                            </div>--}}
{{--                        </a>--}}
{{--                        <a href="#" class="imgCatCrd transitionCls">--}}
{{--                            <div class="imgCatTop position-relative">--}}
{{--                                <img--}}
{{--                                    src="../asset/img/img09.jpg"--}}
{{--                                    class="position-relative"--}}
{{--                                    alt="img"--}}
{{--                                />--}}
{{--                                <div class="position-absolute">--}}
{{--                                    <i>00:15</i><span class="icon-play-arrow-rounded"></span>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="imgCatTxt text-end">--}}
{{--                                <span>فرهنگ و ادب</span>--}}
{{--                                <h2>نخستین همایش ملی فرماندهی و مدیریت در جنگ‌های آینده</h2>--}}
{{--                            </div>--}}
{{--                        </a>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </section>--}}

{{--    <section class="recentPicSec">--}}
{{--        <div class="container">--}}
{{--            <div class="row">--}}
{{--                <div class="col-12">--}}
{{--                    <div class="rcntPicHed d-flex align-items-center">--}}
{{--                        <div class="d-flex align-items-center justify-content-start">--}}
{{--                            <span class="d-block"></span>--}}
{{--                            <p>تصاویر اخیر</p>--}}
{{--                        </div>--}}
{{--                        <a href="#" class="d-flex align-items-center justify-content-end">--}}
{{--                            <i class="transitionCls">دیدن همه</i>--}}
{{--                            <span class="icon-Group-2210"></span>--}}
{{--                        </a>--}}
{{--                    </div>--}}
{{--                    <div class="rcntPicBdy">--}}
{{--                        <a href="#" class="rcntPicCrd">--}}
{{--                            <div class="rcntPicTop position-relative">--}}
{{--                                <img--}}
{{--                                    src="../asset/img/img10.jpg"--}}
{{--                                    class="position-relative transitionCls"--}}
{{--                                    alt="img"--}}
{{--                                />--}}
{{--                                <div class="position-absolute">--}}
{{--                                    <i>00:15</i><span class="icon-play-arrow-rounded"></span>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="rcntPicTxt text-end">--}}
{{--                                <h2>برق و انرژی غزه از کجا تامین می‌شود</h2>--}}
{{--                                <p>--}}
{{--                                    به مناسبت سی و یکمین هفته کتاب فروش حضوری کتابفروشی‌های عضو--}}
{{--                                    «بازار کتاب» با یارانه 25 ...--}}
{{--                                </p>--}}
{{--                            </div>--}}
{{--                        </a>--}}
{{--                        <a href="#" class="rcntPicCrd">--}}
{{--                            <div class="rcntPicTop position-relative">--}}
{{--                                <img--}}
{{--                                    src="../asset/img/img11.jpg"--}}
{{--                                    class="position-relative transitionCls"--}}
{{--                                    alt="img"--}}
{{--                                />--}}
{{--                                <div class="position-absolute">--}}
{{--                                    <i>00:15</i><span class="icon-play-arrow-rounded"></span>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="rcntPicTxt text-end">--}}
{{--                                <h2>برق و انرژی غزه از کجا تامین می‌شود</h2>--}}
{{--                                <p>--}}
{{--                                    به مناسبت سی و یکمین هفته کتاب فروش حضوری کتابفروشی‌های عضو--}}
{{--                                    «بازار کتاب» با یارانه 25 ...--}}
{{--                                </p>--}}
{{--                            </div>--}}
{{--                        </a>--}}
{{--                        <a href="#" class="rcntPicCrd">--}}
{{--                            <div class="rcntPicTop position-relative">--}}
{{--                                <img--}}
{{--                                    src="../asset/img/img10.jpg"--}}
{{--                                    class="position-relative transitionCls"--}}
{{--                                    alt="img"--}}
{{--                                />--}}
{{--                                <div class="position-absolute">--}}
{{--                                    <i>00:15</i><span class="icon-play-arrow-rounded"></span>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="rcntPicTxt text-end">--}}
{{--                                <h2>برق و انرژی غزه از کجا تامین می‌شود</h2>--}}
{{--                                <p>--}}
{{--                                    به مناسبت سی و یکمین هفته کتاب فروش حضوری کتابفروشی‌های عضو--}}
{{--                                    «بازار کتاب» با یارانه 25 ...--}}
{{--                                </p>--}}
{{--                            </div>--}}
{{--                        </a>--}}
{{--                        <a href="#" class="rcntPicCrd">--}}
{{--                            <div class="rcntPicTop position-relative">--}}
{{--                                <img--}}
{{--                                    src="../asset/img/img11.jpg"--}}
{{--                                    class="position-relative transitionCls"--}}
{{--                                    alt="img"--}}
{{--                                />--}}
{{--                                <div class="position-absolute">--}}
{{--                                    <i>00:15</i><span class="icon-play-arrow-rounded"></span>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="rcntPicTxt text-end">--}}
{{--                                <h2>برق و انرژی غزه از کجا تامین می‌شود</h2>--}}
{{--                                <p>--}}
{{--                                    به مناسبت سی و یکمین هفته کتاب فروش حضوری کتابفروشی‌های عضو--}}
{{--                                    «بازار کتاب» با یارانه 25 ...--}}
{{--                                </p>--}}
{{--                            </div>--}}
{{--                        </a>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </section>--}}

{{--    <section class="recentPicSec">--}}
{{--        <div class="container">--}}
{{--            <div class="row">--}}
{{--                <div class="col-12">--}}
{{--                    <div class="rcntPicHed d-flex align-items-center">--}}
{{--                        <div class="d-flex align-items-center justify-content-start">--}}
{{--                            <span class="d-block"></span>--}}
{{--                            <p>تصاویر پربازدید</p>--}}
{{--                        </div>--}}
{{--                        <a href="#" class="d-flex align-items-center justify-content-end">--}}
{{--                            <i class="transitionCls">دیدن همه</i>--}}
{{--                            <span class="icon-Group-2210"></span>--}}
{{--                        </a>--}}
{{--                    </div>--}}
{{--                    <div class="rcntPicBdy">--}}
{{--                        <a href="#" class="rcntPicCrd">--}}
{{--                            <div class="rcntPicTop position-relative">--}}
{{--                                <img--}}
{{--                                    src="../asset/img/img10.jpg"--}}
{{--                                    class="position-relative transitionCls"--}}
{{--                                    alt="img"--}}
{{--                                />--}}
{{--                                <div class="position-absolute">--}}
{{--                                    <i>00:15</i><span class="icon-play-arrow-rounded"></span>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="rcntPicTxt text-end">--}}
{{--                                <h2>برق و انرژی غزه از کجا تامین می‌شود</h2>--}}
{{--                                <p>--}}
{{--                                    به مناسبت سی و یکمین هفته کتاب فروش حضوری کتابفروشی‌های عضو--}}
{{--                                    «بازار کتاب» با یارانه 25 ...--}}
{{--                                </p>--}}
{{--                            </div>--}}
{{--                        </a>--}}
{{--                        <a href="#" class="rcntPicCrd">--}}
{{--                            <div class="rcntPicTop position-relative">--}}
{{--                                <img--}}
{{--                                    src="../asset/img/img11.jpg"--}}
{{--                                    class="position-relative transitionCls"--}}
{{--                                    alt="img"--}}
{{--                                />--}}
{{--                                <div class="position-absolute">--}}
{{--                                    <i>00:15</i><span class="icon-play-arrow-rounded"></span>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="rcntPicTxt text-end">--}}
{{--                                <h2>برق و انرژی غزه از کجا تامین می‌شود</h2>--}}
{{--                                <p>--}}
{{--                                    به مناسبت سی و یکمین هفته کتاب فروش حضوری کتابفروشی‌های عضو--}}
{{--                                    «بازار کتاب» با یارانه 25 ...--}}
{{--                                </p>--}}
{{--                            </div>--}}
{{--                        </a>--}}
{{--                        <a href="#" class="rcntPicCrd">--}}
{{--                            <div class="rcntPicTop position-relative">--}}
{{--                                <img--}}
{{--                                    src="../asset/img/img10.jpg"--}}
{{--                                    class="position-relative transitionCls"--}}
{{--                                    alt="img"--}}
{{--                                />--}}
{{--                                <div class="position-absolute">--}}
{{--                                    <i>00:15</i><span class="icon-play-arrow-rounded"></span>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="rcntPicTxt text-end">--}}
{{--                                <h2>برق و انرژی غزه از کجا تامین می‌شود</h2>--}}
{{--                                <p>--}}
{{--                                    به مناسبت سی و یکمین هفته کتاب فروش حضوری کتابفروشی‌های عضو--}}
{{--                                    «بازار کتاب» با یارانه 25 ...--}}
{{--                                </p>--}}
{{--                            </div>--}}
{{--                        </a>--}}
{{--                        <a href="#" class="rcntPicCrd">--}}
{{--                            <div class="rcntPicTop position-relative">--}}
{{--                                <img--}}
{{--                                    src="../asset/img/img11.jpg"--}}
{{--                                    class="position-relative transitionCls"--}}
{{--                                    alt="img"--}}
{{--                                />--}}
{{--                                <div class="position-absolute">--}}
{{--                                    <i>00:15</i><span class="icon-play-arrow-rounded"></span>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="rcntPicTxt text-end">--}}
{{--                                <h2>برق و انرژی غزه از کجا تامین می‌شود</h2>--}}
{{--                                <p>--}}
{{--                                    به مناسبت سی و یکمین هفته کتاب فروش حضوری کتابفروشی‌های عضو--}}
{{--                                    «بازار کتاب» با یارانه 25 ...--}}
{{--                                </p>--}}
{{--                            </div>--}}
{{--                        </a>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </section>--}}
@endsection
