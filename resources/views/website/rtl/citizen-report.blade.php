@extends('website.rtl.partials.base')

@section('style')
    <link rel="stylesheet" href="{{ asset('asset/css/news-rtl.css') }}"/>
    <style>
        .czSec { padding: 30px 0; }
        .czHead { display: flex; align-items: center; gap: 8px; margin-bottom: 16px; }
        .czHead strong { font-size: 18px; }
        .czIntro { font-size: 14px; color: #555; line-height: 2; margin-bottom: 20px; }
        .czFormBox { border: 1px solid #e3e3e3; border-radius: 10px; padding: 20px; background: #fff; }
        .czFormBox h2 { font-size: 17px; margin-bottom: 8px; }
        .czRules { border: 1px solid #f0d9a8; background: #fdf6e7; border-radius: 10px; padding: 16px 18px; margin-bottom: 20px; }
        .czRules h3 { font-size: 15px; margin-bottom: 8px; }
        .czRules ul { margin: 0; padding-right: 18px; }
        .czRules li { font-size: 13px; color: #6b5b2e; line-height: 2; }
        .czSubmitBtn { background: #0a7d5f; color: #fff; padding: 8px 30px; }
        .czHint { font-size: 12px; color: #888; }
    </style>
@endsection

@section('content')
    <section class="czSec">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="czHead">
                        <strong>شهروند خبرنگار</strong>
                    </div>

                    <p class="czIntro">
                        اگر شاهد رویدادی خبری هستید، گزارش خود را همراه با عکس یا فیلم از طریق فرم زیر برای
                        تحریریه صبح ساحل ارسال کنید. گزارش‌های دریافتی پس از بررسی و راستی‌آزمایی توسط تحریریه،
                        با ذکر عنوان «ارسالی شهروند خبرنگار» منتشر می‌شوند.
                    </p>

                    <div class="czRules">
                        <h3>ضوابط ارسال</h3>
                        <ul>
                            <li>گزارش باید واقعی، مستند و مربوط به رویدادی عینی باشد؛ از ارسال شایعه و مطالب تأییدنشده خودداری کنید.</li>
                            <li>عکس‌ها و فیلم باید توسط خود شما تهیه شده باشد و حقوق دیگران (از جمله حریم خصوصی افراد) را نقض نکند.</li>
                            <li>حداکثر {{ (int) config('citizen-report.files.max_files', 4) }} فایل شامل عکس (JPG/PNG هر کدام حداکثر ۴ مگابایت) و یک ویدیوی MP4 (حداکثر ۳۰ مگابایت) قابل ارسال است.</li>
                            <li>شماره موبایل شما فقط برای هماهنگی تحریریه استفاده می‌شود و منتشر نخواهد شد.</li>
                            <li>تحریریه در ویرایش، تلخیص یا عدم انتشار گزارش‌های دریافتی آزاد است.</li>
                        </ul>
                    </div>

                    <div class="czFormBox">
                        <h2>ارسال گزارش</h2>

                        @if (session('citizen_report_success'))
                            <div class="alert alert-success" role="alert">
                                {{ session('citizen_report_success') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger" role="alert">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('website.rtl.citizen_report.store') }}"
                              enctype="multipart/form-data" class="row g-3">
                            @csrf

                            {{-- Honeypot: hidden from real users --}}
                            <div style="position:absolute;right:-9999px;" aria-hidden="true">
                                <label for="cz-website">وب‌سایت</label>
                                <input type="text" id="cz-website" name="website" tabindex="-1" autocomplete="off">
                            </div>

                            <div class="col-md-4">
                                <label for="cz-name" class="form-label">نام و نام خانوادگی *</label>
                                <input type="text" name="name" id="cz-name" class="form-control"
                                       value="{{ old('name') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="cz-mobile" class="form-label">شماره موبایل *</label>
                                <input type="text" name="mobile" id="cz-mobile" class="form-control" dir="ltr"
                                       placeholder="09xxxxxxxxx" value="{{ old('mobile') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="cz-email" class="form-label">ایمیل (اختیاری)</label>
                                <input type="email" name="email" id="cz-email" class="form-control"
                                       value="{{ old('email') }}">
                            </div>
                            <div class="col-md-8">
                                <label for="cz-title" class="form-label">عنوان گزارش *</label>
                                <input type="text" name="title" id="cz-title" class="form-control"
                                       value="{{ old('title') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="cz-location" class="form-label">محل وقوع رویداد (اختیاری)</label>
                                <input type="text" name="location" id="cz-location" class="form-control"
                                       value="{{ old('location') }}">
                            </div>
                            <div class="col-12">
                                <label for="cz-body" class="form-label">متن گزارش *</label>
                                <textarea name="body" id="cz-body" class="form-control" rows="6"
                                          required>{{ old('body') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="cz-photos" class="form-label">تصاویر (اختیاری)</label>
                                <input type="file" name="photos[]" id="cz-photos" class="form-control" multiple
                                       accept=".jpg,.jpeg,.png,image/jpeg,image/png">
                                <div class="czHint">حداکثر {{ (int) config('citizen-report.files.max_photos', 4) }} تصویر JPG یا PNG، هر کدام تا ۴ مگابایت</div>
                            </div>
                            <div class="col-md-6">
                                <label for="cz-video" class="form-label">ویدیو (اختیاری)</label>
                                <input type="file" name="video" id="cz-video" class="form-control"
                                       accept=".mp4,video/mp4">
                                <div class="czHint">یک فایل MP4 تا ۳۰ مگابایت</div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn transitionCls czSubmitBtn">ارسال گزارش</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
