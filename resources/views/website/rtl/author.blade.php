@extends('website.rtl.partials.base')

@section('style')
    <link rel="stylesheet" href="{{ asset('asset/css/archive-rtl.css') }}">
    <style>
        .authorTypeBadge {
            display: inline-block;
            margin-top: 8px;
            padding: 3px 14px;
            border-radius: 20px;
            background: var(--main-color, #0f766e);
            color: #fff;
            font-size: 13px;
        }

        .authorSocial {
            display: flex;
            justify-content: center;
            gap: 12px;
            list-style: none;
            padding: 10px 0 0;
            margin: 0;
        }

        .authorSocial li a {
            font-size: 20px;
            color: var(--text-1);
        }

        .authorResume .authorResumeBody {
            text-align: right;
            line-height: 2;
        }

        .authorWorkHistory .workHistoryTimeline {
            list-style: none;
            padding: 0;
            margin: 10px 0 0;
            text-align: right;
        }

        .authorWorkHistory .workHistoryTimeline li {
            position: relative;
            padding-right: 18px;
            margin-bottom: 12px;
            border-right: 2px solid var(--main-color, #0f766e);
        }

        .authorWorkHistory .workHistoryTimeline li:before {
            content: '';
            position: absolute;
            right: -6px;
            top: 6px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--main-color, #0f766e);
        }

        .authorWorkHistory .workHistoryTimeline li p {
            margin: 0;
        }

        .authorWorkHistory .workHistoryTimeline li i {
            font-style: normal;
            font-size: 12px;
            opacity: .7;
        }

        .authorWorkHistory strong.d-block {
            display: block;
        }
    </style>
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
                                        {{ $user['name'] }}
                                    </h1>
                                    <p>
                                        {{ $user['nik_name'] }}
                                    </p>
                                    @if(!empty($user['type_label']))
                                        <span class="authorTypeBadge">
                                            {{ $user['type_label'] }}
                                        </span>
                                    @endif
                                </div>
                                @if(!empty($user['social_links']))
                                    <ul class="fotrSocial authorSocial">
                                        @foreach($user['social_links'] as $social_link)
                                            @if(!empty($social_link['network']) && !empty($social_link['url']))
                                                @php
                                                    $social_icon = [
                                                        'instagram' => 'icon-Instagram',
                                                        'telegram' => 'icon-Telegram',
                                                        'x' => 'icon-Twitter-X-1',
                                                        'linkedin' => 'icon-Linkedin',
                                                        'whatsapp' => 'icon-Whatsapp',
                                                    ][$social_link['network']] ?? null;
                                                @endphp
                                                @if($social_icon)
                                                    <li>
                                                        <a href="{{ $social_link['url'] }}" target="_blank" rel="nofollow noopener" class="transitionCls">
                                                            <span class="{{ $social_icon }}"></span>
                                                        </a>
                                                    </li>
                                                @endif
                                            @endif
                                        @endforeach
                                    </ul>
                                @endif
{{--                                @guest--}}
{{--                                    <a href="{{ route('profile.login') }}" class="follow transitionCls">--}}
{{--                                        <span class="icon-Group-2330"></span>--}}
{{--                                        <i>دنبال کنید</i>--}}
{{--                                    </a>--}}
{{--                                @endguest--}}

{{--                                @auth--}}
{{--                                    @if($has_follow)--}}
{{--                                        <a @click.prevent="author({{$user['id']}})" class="follow transitionCls" ref="follow_btn">--}}
{{--                                            <span class="icon-Group-2330"></span>--}}
{{--                                            <i>دنبال میکنید</i>--}}
{{--                                        </a>--}}
{{--                                    @else--}}
{{--                                        <a @click.prevent="author({{$user['id']}})" class="follow transitionCls" ref="follow_btn">--}}
{{--                                            <span class="icon-Group-2330"></span>--}}
{{--                                            <i>دنبال کنید</i>--}}
{{--                                        </a>--}}
{{--                                    @endif--}}
{{--                                @endauth--}}
                            </div>
                            @if($user['bio'] != null && $user['bio'] != '')
                                <div class="authorBio text-center">
                                    <strong>بیوگرافی</strong>
                                    <p>
                                        {{ $user['bio'] }}
                                    </p>
                                </div>
                            @endif
                            @if(!empty($user['resume']))
                                <div class="authorBio authorResume text-center">
                                    <strong>رزومه</strong>
                                    <div class="authorResumeBody">
                                        {!! $user['resume'] !!}
                                    </div>
                                </div>
                            @endif
                            @if(!empty($user['work_history']))
                                <div class="authorBio authorWorkHistory">
                                    <strong class="text-center d-block">سوابق کاری</strong>
                                    <ul class="workHistoryTimeline">
                                        @foreach($user['work_history'] as $work)
                                            @if(!empty($work['title']))
                                                <li>
                                                    <p>
                                                        <b>{{ $work['title'] }}</b>
                                                        @if(!empty($work['organization']))
                                                            <span> — {{ $work['organization'] }}</span>
                                                        @endif
                                                    </p>
                                                    @if(!empty($work['from']) || !empty($work['to']))
                                                        <i>
                                                            {{ !empty($work['from']) ? $work['from'] : '' }}
                                                            تا
                                                            {{ !empty($work['to']) ? $work['to'] : 'اکنون' }}
                                                        </i>
                                                    @endif
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <div class="authorLink">
                                <strong class="text-center">لینک کوتاه</strong>
                                <a href="{{ route('website.rtl.author',['id' => $user['id'],'user_type' => $user['user_type'] ?? 'author']) }}">
                                    <p>
                                        {{ route('website.rtl.author',['id' => $user['id'],'user_type' => $user['user_type'] ?? 'author']) }}
                                    </p>
                                    <span class="icon-Vector-3011"></span>
                                </a>
                            </div>
                        </div>
                        <div class="left">
                            <h2>مطالب نویسنده</h2>
                            @foreach($posts as $item)
                                <a href="{{ $item['url'] }}" class="authorNote transitionCls">
                                    <div class="position-relative">
                                        {{ $item['title'] }}
                                    </div>
                                    <p>
                                        <i>
                                            {{ $item['posted_at_jalali'] }}
                                        </i>
                                        <span class="icon-Group-2321"></span>
                                    </p>
                                </a>
                            @endforeach

                            {{ $posts->links('vendor.pagination.custom') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
