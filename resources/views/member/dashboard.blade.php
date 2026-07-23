@extends('member.layouts.app')

@section('title', 'داشبورد')

@push('styles')
<style>
  .welcome { background: linear-gradient(150deg, var(--plum), var(--brand)); color:#fff; border-radius: var(--r-xl); padding: 26px 28px; position: relative; overflow: hidden; margin-bottom: 22px; }
  .welcome::before { content:""; position:absolute; inset:0; background-image: repeating-linear-gradient(135deg, rgba(255,255,255,.06) 0 2px, transparent 2px 12px);}
  .welcome .row { position: relative; display:flex; align-items:center; gap:18px; flex-wrap:wrap; }
  .welcome h1 { font-size: 23px; }
  .welcome p { opacity:.9; margin-top:6px; }
  .welcome .acts { margin-inline-start:auto; display:flex; gap:10px; flex-wrap:wrap; }
  .welcome .btn-ghost { color:#fff; border-color: rgba(255,255,255,.5); }
  .welcome .btn-ghost:hover { background: rgba(255,255,255,.14); }
  .grid2 { display:grid; grid-template-columns: 1fr 340px; gap: 22px; align-items:start; }
  .ring { --p:0; width:96px;height:96px;border-radius:50%; flex:none;
    background: conic-gradient(var(--brand) calc(var(--p)*1%), var(--surface-2) 0);
    display:grid; place-items:center; position:relative;}
  .ring::before{content:"";position:absolute;inset:9px;border-radius:50%;background:var(--surface);}
  .ring b{position:relative;font-size:22px;font-weight:800;}
  .quick { display:flex; align-items:center; gap:12px; padding:12px 0; border-bottom:1px solid var(--border); }
  .quick:last-child { border-bottom:none; }
  .quick .qi { width:36px; height:36px; border-radius:10px; display:grid; place-items:center; flex:none; }
  .quick .qi svg { width:18px; height:18px; }
  .quick h4 { font-size:14px; }
  .quick p { font-size:12.5px; color:var(--text-muted); margin-top:2px; }
  @media(max-width:880px){.grid2{grid-template-columns:1fr;}}
</style>
@endpush

@section('content')
  <div class="welcome">
    <div class="row">
      <div>
        <h1>{{ $greeting }}</h1>
        <p>امروز {{ $todayJalali }} — به باشگاه اعضای صبح ساحل خوش آمدید.</p>
      </div>
      <div class="acts">
        <a class="btn btn-ghost" href="{{ route('member.archive') }}"><span data-icon="download" style="display:contents"></span> نسخه دیجیتال روزنامه</a>
        <a class="btn btn-gold" href="{{ route('member.club') }}"><span data-icon="gift" style="display:contents"></span> باشگاه مشتریان</a>
      </div>
    </div>
  </div>

  {{-- کارت‌های آمار — امتیاز/سطح فعلاً صفر ایستا هستند و در فاز ۲ (موتور
       امتیاز) از MemberDashboardController@pointsSummary پر می‌شوند. --}}
  <div class="stat-grid" style="margin-bottom:22px">
    <div class="stat phase2" data-phase2="points">
      <div class="ic ic-gold" data-icon="coins"></div>
      <div class="num tnum">{{ number_format((int) $points['total']) }}</div>
      <div class="cap">امتیاز کل</div>
    </div>
    <div class="stat phase2" data-phase2="level">
      <div class="ic ic-brand" data-icon="crown"></div>
      <div class="num">{{ $points['level_name'] ?? '—' }}</div>
      <div class="cap">سطح عضویت</div>
    </div>
    <div class="stat">
      <div class="ic ic-plum" data-icon="clock"></div>
      <div class="num tnum">{{ $subscription['has_active'] ? $subscription['days_left'] . ' روز' : '—' }}</div>
      <div class="cap">اشتراک فعال باقی‌مانده</div>
    </div>
    <div class="stat phase2" data-phase2="library">
      <div class="ic ic-green" data-icon="bookmark"></div>
      <div class="num tnum">{{ $points['saved_news'] }}</div>
      <div class="cap">اخبار ذخیره‌شده</div>
    </div>
  </div>

  <div class="grid2">
    <div style="display:flex;flex-direction:column;gap:22px">
      <div class="card pad">
        <div class="card-h"><span class="ic ic-brand" style="width:34px;height:34px;border-radius:9px" data-icon="target"></span><h3>دسترسی سریع</h3></div>
        <div class="quick">
          <span class="qi ic-gold" data-icon="coins"></span>
          <div style="flex:1"><h4>امتیازها و سطوح</h4><p>راه‌های کسب امتیاز و تاریخچه تراکنش‌ها — به‌زودی</p></div>
          <a class="btn btn-ghost btn-sm" href="{{ route('member.points') }}">مشاهده</a>
        </div>
        <div class="quick">
          <span class="qi ic-plum" data-icon="gift"></span>
          <div style="flex:1"><h4>باشگاه مشتریان</h4><p>ماموریت روزانه، زنجیره ورود و چرخ شانس — به‌زودی</p></div>
          <a class="btn btn-ghost btn-sm" href="{{ route('member.club') }}">مشاهده</a>
        </div>
        <div class="quick">
          <span class="qi ic-brand" data-icon="news"></span>
          <div style="flex:1"><h4>نسخه دیجیتال روزنامه</h4><p>آرشیو PDF روزنامه صبح ساحل</p></div>
          <a class="btn btn-ghost btn-sm" href="{{ route('website.rtl.archive') }}">آرشیو سایت</a>
        </div>
        <div class="quick">
          <span class="qi ic-green" data-icon="settings"></span>
          <div style="flex:1"><h4>تکمیل پروفایل</h4><p>نام، شهر و عکس پروفایل خود را کامل کنید</p></div>
          <a class="btn btn-ghost btn-sm" href="{{ route('member.settings') }}">تنظیمات</a>
        </div>
      </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:22px">
      <div class="card pad">
        <div class="card-h"><h3>اشتراک من</h3></div>
        @if ($subscription['has_active'])
          <div style="display:flex;align-items:center;gap:14px">
            <div class="ring" style="--p:{{ $subscription['progress'] }}"><b class="tnum">{{ $subscription['days_left'] }}</b></div>
            <div>
              <span class="tier-badge tier-gold"><span data-icon="crown" style="display:contents"></span> {{ $subscription['plan_name'] }}</span>
              <p style="margin-top:8px;color:var(--text-muted);font-size:13px">
                {{ $subscription['days_left'] }} روز باقی‌مانده
                @if ($subscription['renews_at_jalali'])
                  <br>پایان اشتراک: {{ $subscription['renews_at_jalali'] }}
                @endif
              </p>
            </div>
          </div>
          <a class="btn btn-plum btn-block" href="{{ route('member.subscription') }}" style="margin-top:16px">مدیریت اشتراک</a>
        @else
          <p style="color:var(--text-muted);font-size:13.5px;margin-bottom:14px">
            اشتراک فعالی برای شماره موبایل شما ثبت نشده است. با تهیه اشتراک ویژه به آرشیو کامل نشریات دسترسی پیدا کنید.
          </p>
          <a class="btn btn-plum btn-block" href="{{ route('website.rtl.subscribe') }}">تهیه اشتراک ویژه</a>
        @endif
      </div>

      {{-- پیشرفت سطح — phase-2 placeholder (points engine). --}}
      <div class="card pad phase2" data-phase2="level-progress">
        <div class="card-h"><h3>پیشرفت تا سطح بعد</h3></div>
        <p style="font-size:13px;color:var(--text-muted);margin-bottom:8px">سطح‌بندی اعضا با راه‌اندازی موتور امتیاز فعال می‌شود.</p>
        <div class="progress"><i style="width:{{ $points['progress'] }}%"></i></div>
        <div style="display:flex;justify-content:space-between;font-size:11.5px;color:var(--text-muted);margin-top:6px">
          <span class="tnum">{{ $points['total'] }}</span><span>به‌زودی</span>
        </div>
        <a class="btn btn-ghost btn-block btn-sm" href="{{ route('member.points') }}" style="margin-top:14px">راه‌های کسب امتیاز</a>
      </div>
    </div>
  </div>
@endsection
