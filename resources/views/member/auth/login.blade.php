<!DOCTYPE html>
<html lang="fa" dir="rtl" data-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>ورود و ثبت‌نام | صبح ساحل</title>
<link rel="stylesheet" href="{{ asset('asset/member/styles.css') }}">
<script>try{document.documentElement.setAttribute('data-theme',localStorage.getItem('ss-theme')||'light')}catch(e){}</script>
<style>
  body { background: var(--canvas); }
  .auth { min-height: 100dvh; display: grid; grid-template-columns: 1.05fr .95fr; }
  .auth-brand {
    position: relative; overflow: hidden; color: #fff; padding: 54px 56px;
    background: linear-gradient(150deg, var(--plum-deep), var(--brand-deep) 75%);
    display: flex; flex-direction: column;
  }
  .auth-brand::before { content:""; position: absolute; inset: 0;
    background-image: repeating-linear-gradient(135deg, rgba(255,255,255,.05) 0 2px, transparent 2px 12px); }
  .auth-brand .word { font-size: 42px; font-weight: 900; position: relative; }
  .auth-brand .sub { opacity: .85; margin-top: 6px; position: relative; }
  .auth-brand .head { margin-top: auto; position: relative; }
  .auth-brand .head h2 { font-size: 30px; line-height: 1.45; font-weight: 800; }
  .auth-brand .head p { opacity: .82; margin-top: 12px; max-width: 38ch; }
  .benefits { position: relative; margin-top: 30px; display: flex; flex-direction: column; gap: 14px; }
  .benefits li { display: flex; align-items: center; gap: 12px; font-weight: 600; }
  .benefits .bi { width: 38px; height: 38px; border-radius: 11px; background: rgba(255,255,255,.16); display: grid; place-items: center; flex: none; }
  .benefits .bi svg { width: 20px; height: 20px; }

  .auth-form { display: grid; place-items: center; padding: 40px 24px; }
  .auth-card { width: 100%; max-width: 420px; }
  .tabs { display: flex; background: var(--surface-2); border-radius: 999px; padding: 5px; margin-bottom: 26px; }
  .tabs button { flex: 1; border: none; background: transparent; padding: 11px; border-radius: 999px; font-weight: 700; font-size: 14px; color: var(--text-muted); display:flex; align-items:center; justify-content:center; gap:7px; }
  .tabs button svg { width: 17px; height: 17px; }
  .tabs button.active { background: var(--surface); color: var(--brand); box-shadow: var(--shadow-sm); }
  .otp-row { display: flex; gap: 8px; direction: ltr; justify-content: center; margin: 6px 0 4px; }
  .otp-row input { width: 46px; height: 56px; text-align: center; font-size: 22px; font-weight: 800; border-radius: var(--r-md); border: 1px solid var(--border-strong); background: var(--surface); color: var(--text); }
  .otp-row input:focus { outline: none; border-color: var(--brand); box-shadow: 0 0 0 3px var(--brand-soft); }
  .divider { display: flex; align-items: center; gap: 12px; color: var(--text-faint); font-size: 12.5px; margin: 22px 0; }
  .divider::before, .divider::after { content:""; flex: 1; height: 1px; background: var(--border); }
  .social { display: grid; gap: 10px; }
  .social button { display: flex; align-items: center; justify-content: center; gap: 10px; padding: 12px; border-radius: var(--r-md); border: 1px solid var(--border-strong); background: var(--surface); color: var(--text); font-weight: 600; font-size: 14px; transition: background .15s; }
  .social .g { width: 19px; height: 19px; }
  .muted-link { text-align: center; margin-top: 20px; color: var(--text-muted); font-size: 13.5px; }
  .muted-link a { color: var(--brand); font-weight: 700; }
  .corner-theme { position: fixed; top: 16px; inset-inline-start: 16px; z-index: 10; }
  .hide { display: none !important; }
  .resend { text-align:center; font-size:13px; color: var(--text-muted); margin-top:14px; }
  .resend button { border:none; background:none; color: var(--brand); font-weight:700; cursor:pointer; font-size:13px; padding:0; }
  .resend button[disabled] { color: var(--text-faint); cursor: default; }
  .field-error { color: var(--accent-red); font-size: 12.5px; font-weight: 600; margin-top: 6px; }
  @media (max-width: 880px) { .auth { grid-template-columns: 1fr; } .auth-brand { display: none; } }
</style>
</head>
@php
    $initialTab = (old('tab') === 'email' || $errors->has('email') || $errors->has('password')) ? 'email' : 'phone';
    $otpStep = filled($otpMobile ?? null);
@endphp
<body @if(session('status')) data-flash="{{ session('status') }}" @endif>
  <button class="icon-btn corner-theme" data-theme-toggle aria-label="تغییر تم" data-icon="sun"></button>

  <div class="auth">
    <div class="auth-brand">
      <div>
        <div class="word">صبح ساحل</div>
        <div class="sub">پایگاه خبری استان هرمزگان</div>
      </div>
      <div class="head">
        <h2>به باشگاه اعضای صبح ساحل بپیوندید</h2>
        <p>اخبار اختصاصی هرمزگان، آرشیو کامل روزنامه، امتیاز و جوایز روزانه — همه در یک حساب.</p>
        <ul class="benefits">
          <li><span class="bi" data-icon="crown"></span>دسترسی کامل به اخبار و آرشیو روزنامه</li>
          <li><span class="bi" data-icon="coins"></span>کسب امتیاز و سطح‌بندی کاربری</li>
          <li><span class="bi" data-icon="gift"></span>چرخ شانس، ماموریت روزانه و قرعه‌کشی</li>
          <li><span class="bi" data-icon="pin"></span>راهنمای گردشگری اختصاصی هرمزگان</li>
        </ul>
      </div>
    </div>

    <div class="auth-form">
      <div class="auth-card">
        <h1 style="font-size:24px; margin-bottom:6px;">ورود / ثبت‌نام</h1>
        <p style="color:var(--text-muted); margin-bottom:22px;">برای ادامه، شماره موبایل یا ایمیل خود را وارد کنید.</p>

        @if (session('status'))
          <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div class="tabs">
          <button type="button" class="{{ $initialTab === 'phone' ? 'active' : '' }}" data-m="phone"><span data-icon="phone" style="display:contents"></span> موبایل</button>
          <button type="button" class="{{ $initialTab === 'email' ? 'active' : '' }}" data-m="email"><span data-icon="mail" style="display:contents"></span> ایمیل</button>
        </div>

        {{-- ورود با موبایل (OTP) --}}
        <div id="pane-phone" class="{{ $initialTab === 'phone' ? '' : 'hide' }}">
          @unless ($otpStep)
            <form method="POST" action="{{ route('member.otp.send') }}">
              @csrf
              <div class="field">
                <label for="mobile">شماره موبایل</label>
                <input class="input tnum" id="mobile" name="mobile" inputmode="tel" autocomplete="tel"
                       placeholder="۰۹۱۲ ۰۰۰ ۰۰۰۰" dir="ltr" style="text-align:right" value="{{ old('mobile') }}">
                @error('mobile')<div class="field-error">{{ $message }}</div>@enderror
              </div>
              <button type="submit" class="btn btn-plum btn-block btn-lg">ارسال کد تأیید</button>
            </form>
          @else
            <form method="POST" action="{{ route('member.otp.verify') }}" id="otp-form">
              @csrf
              <input type="hidden" name="mobile" value="{{ $otpMobile }}">
              <input type="hidden" name="code" id="otp-code">
              <p style="font-size:13.5px; color:var(--text-muted); text-align:center; margin-bottom:14px;">
                کد ۶ رقمی به شماره <b class="tnum" dir="ltr">{{ $otpMobile }}</b> ارسال شد و تا {{ $otpTtl }} دقیقه معتبر است.
              </p>
              <div class="otp-row" id="otp">
                <input maxlength="1" inputmode="numeric" autocomplete="one-time-code"><input maxlength="1" inputmode="numeric"><input maxlength="1" inputmode="numeric"><input maxlength="1" inputmode="numeric"><input maxlength="1" inputmode="numeric"><input maxlength="1" inputmode="numeric">
              </div>
              @error('code')<div class="field-error" style="text-align:center">{{ $message }}</div>@enderror
              <button type="submit" class="btn btn-brand btn-block btn-lg" style="margin-top:16px">تأیید و ورود</button>
            </form>

            <div class="resend">
              <form method="POST" action="{{ route('member.otp.send') }}" style="display:inline">
                @csrf
                <input type="hidden" name="mobile" value="{{ $otpMobile }}">
                <button type="submit" id="resend-btn" disabled>ارسال مجدد کد</button>
              </form>
              <span id="timer-wrap">تا <b class="tnum" id="timer">۰۲:۰۰</b> دیگر</span>
            </div>
            <p class="muted-link"><a href="{{ route('member.otp.reset') }}">تغییر شماره موبایل</a></p>
          @endunless
        </div>

        {{-- ورود با ایمیل و رمز (اعضایی که رمز تعیین کرده‌اند) --}}
        <div id="pane-email" class="{{ $initialTab === 'email' ? '' : 'hide' }}">
          <form method="POST" action="{{ route('member.login.password') }}">
            @csrf
            <input type="hidden" name="tab" value="email">
            <div class="field">
              <label for="email">ایمیل</label>
              <input class="input" type="email" id="email" name="email" autocomplete="email"
                     placeholder="you@example.com" dir="ltr" style="text-align:right" value="{{ old('email') }}">
              @error('email')<div class="field-error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
              <label for="password">رمز عبور</label>
              <input class="input" type="password" id="password" name="password" autocomplete="current-password"
                     placeholder="••••••••" dir="ltr" style="text-align:right">
              @error('password')<div class="field-error">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn btn-plum btn-block btn-lg">ورود</button>
          </form>
          <p class="muted-link" style="margin-top:14px">رمز ندارید؟ با موبایل وارد شوید و در «تنظیمات» رمز تعیین کنید.</p>
        </div>

        {{-- ورود با گوگل/اپل — فاز ۵ (تصمیم ۵-۱): rendered disabled with «به‌زودی» --}}
        <div class="divider">یا ادامه با</div>
        <div class="social">
          <button type="button" class="disabled" disabled aria-disabled="true">
            <svg class="g" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.5 12.3c0-.8-.1-1.5-.2-2.3H12v4.5h5.9a5 5 0 0 1-2.2 3.3v2.7h3.5c2-1.9 3.3-4.7 3.3-8.2Z"/><path fill="#34A853" d="M12 23c3 0 5.5-1 7.3-2.7l-3.5-2.7c-1 .7-2.3 1.1-3.8 1.1-2.9 0-5.4-2-6.3-4.6H2v2.8A11 11 0 0 0 12 23Z"/><path fill="#FBBC05" d="M5.7 14.1a6.6 6.6 0 0 1 0-4.2V7.1H2a11 11 0 0 0 0 9.8l3.7-2.8Z"/><path fill="#EA4335" d="M12 5.4c1.6 0 3 .6 4.2 1.7l3.1-3.1A11 11 0 0 0 2 7.1l3.7 2.8C6.6 7.3 9.1 5.4 12 5.4Z"/></svg>
            ورود با گوگل
            <span class="soon-badge">به‌زودی</span>
          </button>
          <button type="button" class="disabled" disabled aria-disabled="true">
            <svg class="g" viewBox="0 0 24 24" fill="currentColor"><path d="M16 1.5c.1 1-.3 2-.9 2.7-.7.8-1.7 1.4-2.7 1.3-.1-1 .4-2 1-2.6.6-.8 1.7-1.3 2.6-1.4Zm3 16.6c-.5 1.2-.8 1.7-1.4 2.7-.9 1.4-2.2 3.1-3.8 3.1-1.4 0-1.8-.9-3.7-.9s-2.4.9-3.7.9c-1.6 0-2.8-1.5-3.7-2.9C1.4 18 1.1 13.6 2.8 11c.9-1.4 2.4-2.2 3.9-2.2 1.5 0 2.4.9 3.7.9 1.2 0 2-.9 3.7-.9 1.3 0 2.7.7 3.7 2-3.2 1.7-2.7 6.3.5 7.3Z"/></svg>
            ورود با اپل
            <span class="soon-badge">به‌زودی</span>
          </button>
        </div>

        <p class="muted-link">با ورود، <a href="{{ route('website.rtl.about') }}">قوانین و حریم خصوصی</a> صبح ساحل را می‌پذیرید.</p>
      </div>
    </div>
  </div>

  <script src="{{ asset('asset/member/member.js') }}"></script>
  <script>
    // tabs
    document.querySelectorAll('.tabs button').forEach(function (b) {
      b.addEventListener('click', function () {
        document.querySelectorAll('.tabs button').forEach(function (x) { x.classList.remove('active'); });
        b.classList.add('active');
        var m = b.dataset.m;
        document.getElementById('pane-phone').classList.toggle('hide', m !== 'phone');
        document.getElementById('pane-email').classList.toggle('hide', m !== 'email');
      });
    });

    // OTP boxes: accept Persian/Arabic digits, auto-advance, join into the
    // hidden `code` field on submit.
    (function () {
      var boxes = Array.prototype.slice.call(document.querySelectorAll('#otp input'));
      if (!boxes.length) return;

      function normalizeDigits(v) {
        return v
          .replace(/[۰-۹]/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d); })
          .replace(/[٠-٩]/g, function (d) { return '٠١٢٣٤٥٦٧٨٩'.indexOf(d); });
      }

      boxes.forEach(function (inp, i) {
        inp.addEventListener('input', function () {
          var v = normalizeDigits(inp.value).replace(/\D/g, '');
          if (v.length > 1) { // handle paste of the full code
            boxes.forEach(function (b, j) { b.value = v[j] || ''; });
            (boxes[Math.min(v.length, boxes.length) - 1] || inp).focus();
            return;
          }
          inp.value = v;
          if (v && i < boxes.length - 1) boxes[i + 1].focus();
        });
        inp.addEventListener('keydown', function (e) {
          if (e.key === 'Backspace' && !inp.value && i > 0) boxes[i - 1].focus();
        });
      });
      boxes[0].focus();

      document.getElementById('otp-form').addEventListener('submit', function () {
        document.getElementById('otp-code').value = boxes.map(function (b) { return b.value; }).join('');
      });

      // resend countdown ({{ (int) config('login-security.otp.sms_interval_seconds', 120) }}s interval)
      var t = {{ max(30, (int) config('login-security.otp.sms_interval_seconds', 120)) }};
      var el = document.getElementById('timer');
      var btn = document.getElementById('resend-btn');
      var wrap = document.getElementById('timer-wrap');
      var id = setInterval(function () {
        t--;
        var m = String(Math.floor(t / 60)).padStart(2, '0'), s = String(t % 60).padStart(2, '0');
        if (el) el.textContent = window.SS.toFa(m + ':' + s);
        if (t <= 0) {
          clearInterval(id);
          if (btn) btn.disabled = false;
          if (wrap) wrap.style.display = 'none';
        }
      }, 1000);
    })();
  </script>
</body>
</html>
