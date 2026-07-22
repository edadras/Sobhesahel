<div class="pageSideBar">

    @include('website.components.yektanet-ads', ['position' => 'sidebar_top'])

    @if(isset($poll) && $poll != null)
        @include('website.ltr.components.poll')
    @endif

    @php
        $top_advertise = \App\Models\Advertise::getImageAdvertise('sidebar_1',2);
        $bottom_advertise = \App\Models\Advertise::getImageAdvertise('sidebar_2',2);
    @endphp

    @if($top_advertise != null)
        <a href="{{ $top_advertise['url'] }}" class="sideBarImg">
            <img src="{{ asset($top_advertise['image']) }}" alt="img"/>
        </a>
    @endif


    @if(isset($related))
        <div class="sideBox">
            <div class="head">
                <span></span>
                <p>{{ $related_title ?? 'Related news' }}</p>
            </div>
        </div>
        <div class="relNews">
            @foreach($related as $item)
                <a href="{{ $item['url'] }}" class="text-start transitionCls">
                    <img src="{{ asset($item['image_large']) }}" alt="img">
                    <p>
                        {{ $item['title'] }}
                    </p>
                </a>
            @endforeach
        </div>
    @endif

    @if($bottom_advertise != null)
        <a href="{{ $bottom_advertise['url'] }}" class="sideBarImg">
            <img src="{{ asset($bottom_advertise['image']) }}" alt="img"/>
        </a>
    @endif

    @if(isset($most_visited))
        <div class="sideBox">
            <div class="head">
                <span></span>
                <p>Most visited</p>
            </div>
        </div>
        <div class="mostVisitd">
            @foreach($most_visited as $item)
                <a href="{{$item['url']}}" class="text-start">
                    <div>
                        <img src="{{ asset($item['image_large']) }}" class="transitionCls" alt="img">
                    </div>
                    <strong>
                        {{ $item['title'] }}
                    </strong>
                    <p>
                        {{ strip_tags($item['short_description']) }}
                    </p>
                </a>
            @endforeach
        </div>
    @endif

    @if(isset($latest))
        <div class="sideBox">
            <div class="head">
                <span></span>
                <p>{{ $latest_title ?? 'Latest news' }}</p>
            </div>
        </div>
        <div class="latestNews">
            @foreach($latest as $item)
                <a href="{{ $item['url'] }}" class="transitionCls">
                    <p class="position-relative">
                        {{ $item['title'] }}
                    </p>
                </a>
            @endforeach
        </div>
    @endif

    @include('website.components.yektanet-ads', ['position' => 'sidebar_bottom'])
</div>
