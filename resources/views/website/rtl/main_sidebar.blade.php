<div class="pageSideBar">
    <a href="#" class="sideBarImg">
        <img src="/asset/img/img04.jpg" alt="img">
    </a>
    <div class="sideBox">
        <div class="head">
            <span></span>
            <p>تبلیغات متنی</p>
        </div>
    </div>
    <div class="sideBox">
        <div class="sidBarTags">
            <div class="transitionCls">کفش آذر جهان</div>
            <div class="transitionCls">متن تبلیغ</div>
            <div class="transitionCls">خرید دوربین شکاری</div>
            <div class="transitionCls">کفش آذر جهان</div>
            <div class="transitionCls">شیر آلات شودر</div>
            <div class="transitionCls">ارز دیجیتال بیت کوین</div>
        </div>
    </div>
    <div class="sideBox">
        <div class="head">
            <span></span>
            <p>جدیدترین اخبار</p>
        </div>
    </div>
    <div class="latestNews">
        @php
            $latest = \App\Models\Post::getLatestPosts(5,1,'news')->map(function ($item){
      return $item->getPostTotallyForWebsite(1);
});
        @endphp

        @foreach($latest as $item)
            <a href="{{ $item['url'] }}"
               class="transitionCls">
                <p class="position-relative">
                    {{ $item['title'] }}
                </p>
            </a>
        @endforeach
    </div>
    <a href="#" class="sideBarImg">
        <img src="/asset/img/img04.jpg" alt="img">
    </a>
</div>
