@extends('member.layouts.app')

@section('title', 'تنظیمات حساب')

@push('styles')
<style>
  .set-wrap { display:grid; grid-template-columns:220px 1fr; gap:22px; align-items:start; }
  .set-menu { position:sticky; top:calc(var(--header-h) + 24px); }
  .set-menu .card { padding:8px; }
  .set-menu a { display:flex; align-items:center; gap:10px; padding:11px 13px; border-radius:var(--r-md); color:var(--text-muted); font-weight:600; font-size:14px; cursor:pointer; }
  .set-menu a svg { width:18px; height:18px; }
  .set-menu a:hover { background:var(--surface-2); color:var(--text); }
  .set-menu a.active { background:var(--brand-soft); color:var(--brand); }
  .panel { display:none; }
  .panel.active { display:block; }
  .set-sec { margin-bottom:20px; }
  .set-sec .sh { font-size:13px; font-weight:700; color:var(--text-faint); margin-bottom:12px; }
  .row2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
  .avatar-edit { display:flex; align-items:center; gap:16px; margin-bottom:8px; }
  .field-error { color: var(--accent-red); font-size: 12.5px; font-weight: 600; margin-top: 6px; }
  .file-input { font-size:13px; color:var(--text-muted); }
  @media(max-width:820px){ .set-wrap{grid-template-columns:1fr;} .set-menu{position:static;} .set-menu .card{display:flex;overflow-x:auto;} .set-menu a{white-space:nowrap;} .row2{grid-template-columns:1fr;} }
</style>
@endpush

@section('content')
  <div class="breadcrumb"><a href="{{ route('member.dashboard') }}">داشبورد</a> ‹ <span>تنظیمات</span></div>
  <div class="page-head"><h1>تنظیمات حساب</h1><p>اطلاعات شخصی، امنیت و زبان حساب خود را مدیریت کنید</p></div>

  @if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif

  <div class="set-wrap">
    <div class="set-menu">
      <div class="card" id="menu">
        <a class="active" data-go="account"><span data-icon="user" style="display:contents"></span><span>حساب کاربری</span></a>
        <a data-go="security"><span data-icon="settings" style="display:contents"></span><span>امنیت</span></a>
        <a data-go="appearance"><span data-icon="eye" style="display:contents"></span><span>ظاهر و زبان</span></a>
      </div>
    </div>

    <div>
      {{-- حساب کاربری --}}
      <div class="panel active" data-p="account">
        <form method="POST" action="{{ route('member.settings.profile') }}" enctype="multipart/form-data">
          @csrf
          <div class="card pad set-sec">
            <div class="sh">عکس پروفایل</div>
            <div class="avatar-edit">
              <span class="avatar xl">
                @if ($member->avatarUrl())
                  <img src="{{ $member->avatarUrl() }}" alt="{{ $member->fullName() }}">
                @else
                  {{ $member->initials() }}
                @endif
              </span>
              <div style="display:flex;flex-direction:column;gap:8px">
                <input class="file-input" type="file" name="avatar" accept="image/jpeg,image/png,image/webp">
                @error('avatar')<div class="field-error">{{ $message }}</div>@enderror
                @if ($member->avatar)
                  <label style="display:flex;align-items:center;gap:7px;font-size:13px;color:var(--text-muted);cursor:pointer">
                    <input type="checkbox" name="remove_avatar" value="1"> حذف عکس فعلی
                  </label>
                @endif
                <span style="font-size:12px;color:var(--text-faint)">
                  حداکثر {{ round(((int) config('member.avatar.max_kb', 2048)) / 1024, 1) }} مگابایت — {{ implode('، ', (array) config('member.avatar.mimes', [])) }}
                </span>
              </div>
            </div>
          </div>

          <div class="card pad set-sec">
            <div class="sh">اطلاعات شخصی</div>
            <div class="row2">
              <div class="field">
                <label for="first_name">نام</label>
                <input class="input" id="first_name" name="first_name" value="{{ old('first_name', $member->first_name) }}">
                @error('first_name')<div class="field-error">{{ $message }}</div>@enderror
              </div>
              <div class="field">
                <label for="last_name">نام خانوادگی</label>
                <input class="input" id="last_name" name="last_name" value="{{ old('last_name', $member->last_name) }}">
                @error('last_name')<div class="field-error">{{ $message }}</div>@enderror
              </div>
              <div class="field">
                <label>شماره موبایل</label>
                {{-- موبایل شناسه ورود است و از اینجا قابل تغییر نیست --}}
                <input class="input tnum" value="{{ $member->mobile }}" dir="ltr" style="text-align:right" readonly disabled>
                <span style="font-size:12px;color:var(--text-faint)">شماره موبایل شناسه ورود شماست و قابل ویرایش نیست.</span>
              </div>
              <div class="field">
                <label for="email">ایمیل</label>
                <input class="input" type="email" id="email" name="email" dir="ltr" style="text-align:right"
                       placeholder="you@example.com" value="{{ old('email', $member->email) }}">
                @error('email')<div class="field-error">{{ $message }}</div>@enderror
              </div>
              <div class="field">
                <label for="city">شهر (هرمزگان)</label>
                <select class="input" id="city" name="city">
                  <option value="">— انتخاب کنید —</option>
                  @foreach ($cities as $city)
                    <option value="{{ $city }}" @selected(old('city', $member->city) === $city)>{{ $city }}</option>
                  @endforeach
                </select>
                @error('city')<div class="field-error">{{ $message }}</div>@enderror
              </div>
              <div class="field">
                <label for="birth_date">تاریخ تولد (شمسی)</label>
                <input class="input tnum" id="birth_date" name="birth_date" dir="ltr" style="text-align:right"
                       placeholder="۱۳۷۰/۰۳/۱۵" inputmode="numeric" value="{{ old('birth_date', $birthDateJalali) }}">
                @error('birth_date')<div class="field-error">{{ $message }}</div>@enderror
              </div>
            </div>
            <div class="field">
              <label for="bio">درباره من</label>
              <textarea class="input" id="bio" name="bio" rows="3" placeholder="مثلاً: علاقه‌مند به اخبار اقتصادی و گردشگری هرمزگان.">{{ old('bio', $member->bio) }}</textarea>
              @error('bio')<div class="field-error">{{ $message }}</div>@enderror
            </div>
            <div style="display:flex;gap:10px">
              <button type="submit" class="btn btn-brand">ذخیره تغییرات</button>
              <a class="btn btn-ghost" href="{{ route('member.settings') }}">انصراف</a>
            </div>
          </div>
        </form>
      </div>

      {{-- امنیت --}}
      <div class="panel" data-p="security">
        <form method="POST" action="{{ route('member.settings.password') }}">
          @csrf
          <div class="card pad set-sec">
            <div class="sh">{{ $member->hasPassword() ? 'تغییر رمز عبور' : 'تعیین رمز عبور' }}</div>
            @unless ($member->hasPassword())
              <p style="font-size:13px;color:var(--text-muted);margin-bottom:14px">
                هنوز رمزی تعیین نکرده‌اید. با تعیین رمز، امکان ورود با ایمیل و رمز نیز فعال می‌شود.
              </p>
            @endunless
            @if ($member->hasPassword())
              <div class="field">
                <label for="current_password">رمز فعلی</label>
                <input class="input" type="password" id="current_password" name="current_password" placeholder="••••••••" dir="ltr" autocomplete="current-password">
                @error('current_password')<div class="field-error">{{ $message }}</div>@enderror
              </div>
            @endif
            <div class="row2">
              <div class="field">
                <label for="new_password">رمز جدید</label>
                <input class="input" type="password" id="new_password" name="password" placeholder="حداقل ۸ حرف" dir="ltr" autocomplete="new-password">
                @error('password')<div class="field-error">{{ $message }}</div>@enderror
              </div>
              <div class="field">
                <label for="password_confirmation">تکرار رمز جدید</label>
                <input class="input" type="password" id="password_confirmation" name="password_confirmation" placeholder="••••••••" dir="ltr" autocomplete="new-password">
              </div>
            </div>
            <button type="submit" class="btn btn-brand">{{ $member->hasPassword() ? 'به‌روزرسانی رمز' : 'تعیین رمز' }}</button>
          </div>
        </form>

        <div class="card pad set-sec">
          <div class="sh">ورود با پیامک</div>
          <p style="font-size:13.5px;color:var(--text-muted)">
            ورود با کد یک‌بارمصرف پیامکی برای شماره <b class="tnum" dir="ltr">{{ $member->mobile }}</b> همیشه فعال است.
          </p>
        </div>
      </div>

      {{-- ظاهر و زبان --}}
      <div class="panel" data-p="appearance">
        <div class="card pad set-sec">
          <div class="sh">حالت نمایش</div>
          <p style="font-size:13.5px;color:var(--text-muted);margin-bottom:12px">تم روشن/تیره با دکمه بالای صفحه تغییر می‌کند و در همین مرورگر ذخیره می‌شود.</p>
          <button type="button" class="btn btn-ghost btn-sm" data-theme-toggle-alt>تغییر تم روشن / تیره</button>
        </div>

        <form method="POST" action="{{ route('member.settings.locale') }}">
          @csrf
          <div class="card pad set-sec">
            <div class="sh">زبان</div>
            <div class="row2">
              <div class="field">
                <label for="locale">زبان سایت</label>
                <select class="input" id="locale" name="locale">
                  @foreach ($languages as $language)
                    <option value="{{ $language->code }}" @selected(old('locale', $member->locale) === $language->code)>{{ $language->native_name }}</option>
                  @endforeach
                </select>
                @error('locale')<div class="field-error">{{ $message }}</div>@enderror
              </div>
            </div>
            <button type="submit" class="btn btn-brand btn-sm">ذخیره زبان</button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  // settings sub-menu panels
  document.querySelectorAll('#menu a').forEach(function (a) {
    a.addEventListener('click', function () {
      document.querySelectorAll('#menu a').forEach(function (x) { x.classList.remove('active'); });
      a.classList.add('active');
      document.querySelectorAll('.panel').forEach(function (p) { p.classList.toggle('active', p.dataset.p === a.dataset.go); });
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  });

  // open the panel that has validation errors
  @if ($errors->has('current_password') || $errors->has('password'))
    document.querySelector('#menu a[data-go="security"]').click();
  @elseif ($errors->has('locale'))
    document.querySelector('#menu a[data-go="appearance"]').click();
  @endif

  // secondary theme toggle inside the appearance panel
  document.querySelectorAll('[data-theme-toggle-alt]').forEach(function (b) {
    b.addEventListener('click', function () {
      window.SS.applyTheme(document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
    });
  });
</script>
@endpush
