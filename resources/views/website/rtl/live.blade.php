@extends('website.rtl.partials.base')

@section('style')
    <style>
        .livePgSec {
            margin: 30px 0 80px;
        }

        .livePgHead {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .livePgHead h1 {
            font-size: 22px;
            margin: 0;
            color: var(--text-1);
        }

        .liveBadge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #d32f2f;
            color: #fff;
            border-radius: 20px;
            padding: 3px 12px;
            font-size: 13px;
            white-space: nowrap;
        }

        .liveBadge .liveDot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #fff;
            animation: livePulse 1.2s infinite;
        }

        @keyframes livePulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }

        .liveCard {
            border: 1px solid var(--stroke-e-3);
            border-radius: 10px;
            background: var(--white);
            overflow: hidden;
            margin-bottom: 25px;
        }

        .liveCardHead {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 12px 15px;
            flex-wrap: wrap;
        }

        .liveCardHead h2 {
            font-size: 17px;
            margin: 0;
            color: var(--text-1);
        }

        .liveCardMeta {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: var(--text-2);
        }

        .liveEmbed {
            position: relative;
            width: 100%;
            padding-top: 56.25%;
            background: #000;
        }

        .liveEmbed iframe {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }

        .liveCard .liveDesc {
            padding: 12px 15px;
            font-size: 14px;
            color: var(--text-2);
            line-height: 2;
        }

        .liveUpcomingTitle {
            font-size: 18px;
            margin: 30px 0 15px;
            color: var(--text-1);
        }

        .liveEmpty {
            border: 1px dashed var(--stroke-e-3);
            border-radius: 10px;
            padding: 40px 20px;
            text-align: center;
            color: var(--text-2);
        }
    </style>
@endsection

@section('content')
    <section class="livePgSec">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="livePgHead">
                        <div class="homPgTitle">
                            <span></span>
                        </div>
                        <h1>پخش زنده</h1>
                    </div>

                    @if($live_streams->isEmpty() && $upcoming_streams->isEmpty())
                        <div class="liveEmpty">
                            در حال حاضر پخش زنده‌ای در جریان نیست. به‌زودی برنامه‌های زنده در همین صفحه اعلام می‌شود.
                        </div>
                    @endif

                    {{-- Live now --}}
                    @foreach($live_streams as $stream)
                        <div class="liveCard">
                            <div class="liveCardHead">
                                <h2>{{ $stream->title }}</h2>
                                <div class="liveCardMeta">
                                    <span class="liveBadge"><span class="liveDot"></span>پخش زنده</span>
                                    <span>{{ $stream->platform_label }}</span>
                                </div>
                            </div>
                            @if($stream->embed_src)
                                <div class="liveEmbed">
                                    <iframe src="{{ $stream->embed_src }}"
                                            title="{{ $stream->title }}"
                                            loading="lazy"
                                            allow="autoplay; encrypted-media; picture-in-picture; fullscreen"
                                            allowfullscreen
                                            referrerpolicy="no-referrer-when-downgrade"></iframe>
                                </div>
                            @endif
                            @if(!empty($stream->description))
                                <div class="liveDesc">{{ $stream->description }}</div>
                            @endif
                        </div>
                    @endforeach

                    {{-- Upcoming --}}
                    @if($upcoming_streams->isNotEmpty())
                        <h2 class="liveUpcomingTitle">برنامه‌های آینده</h2>
                        @foreach($upcoming_streams as $stream)
                            <div class="liveCard">
                                <div class="liveCardHead">
                                    <h2>{{ $stream->title }}</h2>
                                    <div class="liveCardMeta">
                                        @if($stream->starts_at_jalali)
                                            <span>شروع: {{ $stream->starts_at_jalali }}</span>
                                        @endif
                                        <span>{{ $stream->platform_label }}</span>
                                    </div>
                                </div>
                                @if(!empty($stream->image))
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($stream->image) }}" alt="{{ $stream->title }}"
                                         style="max-width:100%;display:block;">
                                @elseif($stream->embed_src)
                                    <div class="liveEmbed">
                                        <iframe src="{{ $stream->embed_src }}"
                                                title="{{ $stream->title }}"
                                                loading="lazy"
                                                allow="encrypted-media; picture-in-picture; fullscreen"
                                                allowfullscreen
                                                referrerpolicy="no-referrer-when-downgrade"></iframe>
                                    </div>
                                @endif
                                @if(!empty($stream->description))
                                    <div class="liveDesc">{{ $stream->description }}</div>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
