@extends('tv.partials.layout')

@section('content')
    <div class="container">
        <div class="tvSectionTitle">
            <h1>پخش زنده</h1>
        </div>

        @if($live_streams->isEmpty() && $upcoming_streams->isEmpty())
            <div class="tvEmptyBox">
                در حال حاضر پخش زنده‌ای در جریان نیست. به‌زودی برنامه‌های زنده در همین صفحه اعلام می‌شود.
            </div>
        @endif

        {{-- Live now --}}
        @foreach($live_streams as $stream)
            <div class="tvLiveCard">
                <div class="tvLiveCardHead">
                    <h2>{{ $stream->title }}</h2>
                    <div class="tvLiveCardMeta">
                        <span class="tvBadgeLive"><span class="tvLiveDot"></span>پخش زنده</span>
                        <span>{{ $stream->platform_label }}</span>
                    </div>
                </div>
                @if($stream->embed_src)
                    <div class="tvEmbed">
                        <iframe src="{{ $stream->embed_src }}"
                                title="{{ $stream->title }}"
                                loading="lazy"
                                allow="autoplay; encrypted-media; picture-in-picture; fullscreen"
                                allowfullscreen
                                referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                @endif
                @if(!empty($stream->description))
                    <div class="tvDesc">{{ $stream->description }}</div>
                @endif
            </div>
        @endforeach

        {{-- Upcoming --}}
        @if($upcoming_streams->isNotEmpty())
            <div class="tvSectionTitle">
                <h2>برنامه‌های آینده</h2>
            </div>
            @foreach($upcoming_streams as $stream)
                <div class="tvLiveCard">
                    <div class="tvLiveCardHead">
                        <h2>{{ $stream->title }}</h2>
                        <div class="tvLiveCardMeta">
                            @if($stream->starts_at_jalali)
                                <span class="tvBadgeSoon">شروع: {{ $stream->starts_at_jalali }}</span>
                            @endif
                            <span>{{ $stream->platform_label }}</span>
                        </div>
                    </div>
                    @if(!empty($stream->image))
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($stream->image) }}" alt="{{ $stream->title }}"
                             style="max-width:100%;display:block;">
                    @endif
                    @if(!empty($stream->description))
                        <div class="tvDesc">{{ $stream->description }}</div>
                    @endif
                </div>
            @endforeach
        @endif
    </div>
@endsection
