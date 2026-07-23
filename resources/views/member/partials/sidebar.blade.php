@php
    /** @var \App\Models\Member $sideMember */
    $sideMember = auth('member')->user();
    $active = $active ?? '';

    try {
        $memberSince = $sideMember?->created_at
            ? \Morilog\Jalali\Jalalian::fromCarbon($sideMember->created_at)->format('%B %Y')
            : null;
    } catch (\Throwable) {
        $memberSince = null;
    }

    // «کتاب‌ها» intentionally has no entry anywhere (تصمیم ۵-۲).
    $navItems = [
        ['id' => 'dashboard', 'label' => 'داشبورد', 'icon' => 'grid', 'url' => route('member.dashboard')],
        ['id' => 'subscription', 'label' => 'اشتراک من', 'icon' => 'crown', 'url' => route('member.subscription')],
        ['id' => 'points', 'label' => 'امتیازها و سطوح', 'icon' => 'coins', 'url' => route('member.points')],
        ['id' => 'club', 'label' => 'باشگاه مشتریان', 'icon' => 'gift', 'url' => route('member.club')],
        ['id' => 'shop', 'label' => 'فروشگاه دیجیتال', 'icon' => 'bag', 'url' => route('member.shop')],
        ['id' => 'library', 'label' => 'مطالعات من', 'icon' => 'book', 'url' => route('member.library')],
        ['id' => 'badges', 'label' => 'نشان‌ها', 'icon' => 'award', 'url' => route('member.badges')],
        ['id' => 'notifications', 'label' => 'اعلان‌ها', 'icon' => 'bell', 'url' => route('member.notifications')],
        ['id' => 'tourism', 'label' => 'گردشگری هرمزگان', 'icon' => 'pin', 'url' => route('member.tourism')],
        ['id' => 'archive', 'label' => 'نسخه دیجیتال روزنامه', 'icon' => 'news', 'url' => route('member.archive')],
    ];
@endphp
<aside class="sidebar" id="ss-side">
    <div class="side-card side-userbox">
        <span class="avatar xl" style="margin:0 auto">
            @if ($sideMember?->avatarUrl())
                <img src="{{ $sideMember->avatarUrl() }}" alt="{{ $sideMember->fullName() }}">
            @else
                {{ $sideMember?->initials() ?? 'ع' }}
            @endif
        </span>
        <div class="name">{{ $sideMember?->fullName() }}</div>
        @if ($memberSince)
            <div class="meta">عضو از {{ $memberSince }}</div>
        @endif
        <div style="margin-top:10px">
            {{-- سطح عضویت — phase-2 placeholder (levels land with the points engine) --}}
            <span class="tier-badge tier-bronze"><span data-icon="star" style="display:contents"></span> عضو باشگاه</span>
        </div>
        <div class="side-progress">
            <div class="progress"><i style="width:0%"></i></div>
            <div class="lbl"><span class="tnum">۰ امتیاز</span><span>سطح‌بندی به‌زودی</span></div>
        </div>
    </div>

    <nav class="side-card side-nav">
        @foreach ($navItems as $item)
            <a href="{{ $item['url'] }}" class="{{ $active === $item['id'] ? 'active' : '' }}">
                <span data-icon="{{ $item['icon'] }}" style="display:contents"></span>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach

        <div class="sep"></div>

        <a href="{{ route('member.settings') }}" class="{{ $active === 'settings' ? 'active' : '' }}">
            <span data-icon="settings" style="display:contents"></span>
            <span>تنظیمات</span>
        </a>

        <form method="POST" action="{{ route('member.logout') }}">
            @csrf
            <button type="submit" class="navlink">
                <span data-icon="logout" style="display:contents"></span>
                <span>خروج از حساب</span>
            </button>
        </form>
    </nav>
</aside>
