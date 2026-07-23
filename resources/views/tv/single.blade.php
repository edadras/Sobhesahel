@extends('tv.partials.layout')

@section('content')
    <div class="container">
        <div class="tvPlayerBox" style="margin-top:10px;">
            @if(!empty($post->embed))
                {{-- Big player: reuses the video's stored embed (same field the main site renders) --}}
                <div class="tvEmbed">
                    <div class="tvEmbedRaw">
                        {!! $post->embed !!}
                    </div>
                </div>
            @elseif(!empty($post->getImageUrl()))
                <div class="tvEmbed">
                    <img src="{{ $post->getImageUrl() }}" alt="{{ $post->title }}"
                         style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;">
                </div>
            @endif

            <div class="tvPlayerMeta">
                <h1>{{ $post->title }}</h1>
                <div class="tvMetaRow">
                    @if(!empty($post->posted_at_jalali))
                        <time>{{ \App\Models\MarketPrice::toFarsiNumber($post->posted_at_jalali) }}</time>
                    @endif
                    @if(!empty($post->sub_title))
                        <span>{{ $post->sub_title }}</span>
                    @endif
                </div>
                @if(!empty($post->short_description))
                    <div class="tvDesc">{!! $post->short_description !!}</div>
                @endif
                @if(!empty($post->body))
                    <div class="tvDesc">{!! $post->body !!}</div>
                @endif
            </div>
        </div>

        @if($related->isNotEmpty())
            <div class="tvSectionTitle">
                <h2>ویدئوهای بیشتر</h2>
            </div>
            <div class="tvGrid">
                @foreach($related as $item)
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
    </div>
@endsection
