@php
    /** @var \App\Models\Member $headerMember */
    $headerMember = auth('member')->user();
@endphp
<header class="topbar">
    <div class="topbar-inner">
        <button class="icon-btn hamburger" id="ss-burger" aria-label="منو" data-icon="menu"></button>

        <a class="brand-mark" href="{{ route('member.dashboard') }}">
            <div>
                <div class="brand-word">صبح ساحل</div>
                <div class="brand-tag">پنل اعضا و باشگاه مشتریان</div>
            </div>
        </a>

        <nav class="topnav">
            <a href="{{ route('website.home') }}">خانه</a>
            <a href="{{ route('website.rtl.index', ['type' => 'news']) }}">اخبار</a>
            <a href="{{ route('website.rtl.index', ['type' => 'note']) }}">یادداشت‌ها</a>
            <a href="{{ route('website.rtl.archive') }}">آرشیو روزنامه‌ها</a>
            <a href="{{ route('website.rtl.index', ['type' => 'podcast']) }}">پادکست</a>
            <a href="{{ route('website.rtl.index', ['type' => 'photo']) }}">عکس</a>
            <a href="{{ route('website.rtl.index', ['type' => 'video']) }}">ویدئو</a>
        </nav>

        <div class="topbar-spacer"></div>

        <div class="topbar-actions">
            <button class="icon-btn" data-theme-toggle aria-label="تغییر تم" data-icon="sun"></button>

            <a class="icon-btn" href="{{ route('member.notifications') }}" aria-label="اعلان‌ها" data-icon="bell"></a>

            @if ($headerMember)
                <a class="user-chip" href="{{ route('member.points') }}">
                    {{-- امتیاز — phase-2 placeholder (points engine lands in فاز ۲) --}}
                    <span class="pts tnum">۰ <span>امتیاز</span></span>
                    <span class="uname">{{ $headerMember->fullName() }}</span>
                    <span class="avatar">
                        @if ($headerMember->avatarUrl())
                            <img src="{{ $headerMember->avatarUrl() }}" alt="{{ $headerMember->fullName() }}">
                        @else
                            {{ $headerMember->initials() }}
                        @endif
                    </span>
                </a>
            @endif
        </div>
    </div>
</header>
