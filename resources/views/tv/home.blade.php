@extends('tv.partials.layout')

@section('content')
    <div class="container">

        {{-- Live strip (only when a stream is live) --}}
        @php $tv_home_live = $live_streams->filter(fn ($s) => $s->is_live)->values(); @endphp
        @if($tv_home_live->isNotEmpty())
            <div class="tvSectionTitle">
                <h2>پخش زنده</h2>
            </div>
            @foreach($tv_home_live->take(1) as $stream)
                <div class="tvLiveCard">
                    <div class="tvLiveCardHead">
                        <h3>{{ $stream->title }}</h3>
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
                </div>
            @endforeach
        @endif

        {{-- Featured: big player card for the newest video --}}
        @if($featured)
            <div class="tvSectionTitle">
                <h1>تازه‌ترین ویدئو</h1>
            </div>
            <div class="tvPlayerBox">
                <a href="{{ $featured['tv_url'] }}" class="tvCardThumb tvEmbed" style="display:block;padding-top:56.25%;">
                    @if(!empty($featured['image_large']))
                        <img src="{{ $featured['image_large'] }}" alt="{{ $featured['title'] }}"
                             style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;">
                    @endif
                    <span class="tvPlayIco">&#9658;</span>
                </a>
                <div class="tvPlayerMeta">
                    <h2><a href="{{ $featured['tv_url'] }}">{{ $featured['title'] }}</a></h2>
                    <div class="tvMetaRow">
                        @if(!empty($featured['posted_at_jalali']))
                            <time>{{ \App\Models\MarketPrice::toFarsiNumber($featured['posted_at_jalali']) }}</time>
                        @endif
                    </div>
                    @if(!empty($featured['short_description']))
                        <div class="tvDesc">{!! strip_tags((string) $featured['short_description']) !!}</div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Grid of latest videos --}}
        @if($grid->isNotEmpty())
            <div class="tvSectionTitle">
                <h2>آخرین ویدئوها</h2>
            </div>
            <div class="tvGrid">
                @foreach($grid as $item)
                    <a href="{{ $item['tv_url'] }}" class="tvCard">
                        <div class="tvCardThumb">
                            @if(!empty($item['image_medium']))
                                <img src="{{ $item['image_medium'] }}" alt="{{ $item['title'] }}" loading="lazy">
                            @endif
                            <span class="tvPlayIco">&#9658;</span>
                        </div>
                        <div class="tvCardBody">
                            <h3>{{ $item['title'] }}</h3>
                            @if(!empty($item['posted_at_jalali']))
                                <time>{{ \App\Models\MarketPrice::toFarsiNumber($item['posted_at_jalali']) }}</time>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

        @if(!$featured && $grid->isEmpty())
            <div class="tvEmptyBox">هنوز ویدئویی منتشر نشده است.</div>
        @endif
    </div>
@endsection
