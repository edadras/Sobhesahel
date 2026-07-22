<div class="pageSideBar">

    @include('website.components.yektanet-ads', ['position' => 'sidebar_top'])

{{--    @if(isset($poll) && $poll != null)--}}
{{--        @livewire('poll-component', ['poll' => $poll])--}}
{{--    @endif--}}



    @php
        $top_advertise = \App\Models\Advertise::getImageAdvertise('top_advertise_image');
        $bottom_advertise = \App\Models\Advertise::getImageAdvertise('button_advertise_image');
        $text_advertise = \App\Models\Advertise::getTextAdvertise();
    @endphp

    @if($top_advertise != null)
        <a href="{{ $top_advertise['url'] }}" class="sideBarImg" target="_blank">
            <img src="{{ \Illuminate\Support\Facades\Storage::url($top_advertise['image'])  }}" alt="img"/>
        </a>
    @endif

        @if($text_advertise != null && count($text_advertise) != 0)
            <div class="sideBox">
                <div class="head">
                    <span></span>
                    <p>تبلیغات متنی</p>
                </div>
            </div>

            <div class="sideBox">
                <div class="sidBarTags">
                    @foreach($text_advertise as $item)
                        <a href="{{ route('advertise_click',['id' => $item['id']]) }}" target="_blank">
                            <div class="transitionCls">
                                {{ $item['text'] }}
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if(isset($latest))
            <div class="sideBox">
                <div class="head">
                    <span></span>
                    <p>
                        {{ $latest_title ?? 'جدیدترین اخبار' }}
                    </p>
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

    @if(isset($related))
        <div class="sideBox">
            <div class="head">
                <span></span>
                <p>{{ $related_title ?? 'اخبار مرتبط' }}</p>
            </div>
        </div>
        <div class="relNews">
            @foreach($related as $item)
                <a href="{{ $item['url'] }}" class="text-end transitionCls">
                    <img src="{{ asset($item['image_large']) }}" alt="img"/>
                    <p>
                        {{ $item['title'] }}
                    </p>
                </a>
            @endforeach
        </div>
    @endif
        @if($bottom_advertise != null)
            <a href="{{ $bottom_advertise['url'] }}" class="sideBarImg" target="_blank">
                <img src="{{ \Illuminate\Support\Facades\Storage::url($bottom_advertise['image']) }}" alt="img"/>
            </a>
        @endif
    @if(isset($most_visited))
        <div class="sideBox">
            <div class="head">
                <span></span>
                <p>پربازدیدترین ها</p>
            </div>
        </div>
        <div class="mostVisitd">
            @foreach($most_visited as $item)
                <a href="{{ $item['url'] }}" class="text-end">
                    <div>
                        <img
                            src="{{ $item['image_large'] }}"
                            class="transitionCls"
                            alt="img"
                        />
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

    @include('website.components.yektanet-ads', ['position' => 'sidebar_bottom'])

</div>

