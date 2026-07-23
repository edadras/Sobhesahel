@extends('tv.partials.layout')

@section('content')
    <div class="container">
        <div class="tvSectionTitle">
            <h1>{{ $page_title }}</h1>
        </div>

        {{-- Category (tag) filter --}}
        @if($categories->isNotEmpty())
            <div class="tvChips">
                <a href="{{ url()->current() }}" class="{{ $category === '' ? 'active' : '' }}">همه</a>
                @foreach($categories as $cat)
                    @php
                        $catName = rescue(fn () => (string) ($cat->getTranslation('name', 'fa') ?: $cat->name), (string) $cat->name, false);
                    @endphp
                    @continue($catName === '')
                    <a href="{{ url()->current() . '?category=' . urlencode($catName) }}"
                       class="{{ $category === $catName ? 'active' : '' }}">{{ $catName }}</a>
                @endforeach
            </div>
        @endif

        @if($items->isEmpty())
            <div class="tvEmptyBox">ویدئویی برای نمایش یافت نشد.</div>
        @else
            <div class="tvGrid">
                @foreach($items as $item)
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

            {{-- Pagination (Persian digits) --}}
            @if($videos->hasPages())
                <nav class="tvPagination" aria-label="صفحه‌بندی">
                    @if($videos->onFirstPage())
                        <span>صفحه قبل</span>
                    @else
                        <a href="{{ $videos->previousPageUrl() }}">صفحه قبل</a>
                    @endif

                    <span class="tvPageInfo">
                        صفحه {{ \App\Models\MarketPrice::toFarsiNumber($videos->currentPage()) }}
                        از {{ \App\Models\MarketPrice::toFarsiNumber($videos->lastPage()) }}
                    </span>

                    @if($videos->hasMorePages())
                        <a href="{{ $videos->nextPageUrl() }}">صفحه بعد</a>
                    @else
                        <span>صفحه بعد</span>
                    @endif
                </nav>
            @endif
        @endif
    </div>
@endsection
