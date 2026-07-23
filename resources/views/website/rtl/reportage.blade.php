@extends('website.rtl.partials.base')

@section('style')
    <style>
        .rptSec {
            margin: 30px 0 50px;
        }

        .rptSec h1 {
            font-size: 22px;
            color: var(--text-1);
            margin-bottom: 15px;
        }

        .rptIntro {
            border: 1px solid var(--stroke-e-3);
            border-radius: 8px;
            background: var(--white);
            padding: 20px;
            margin-bottom: 25px;
            line-height: 2;
            font-size: 14px;
            color: var(--text-1);
        }

        .rptIntro ul {
            margin: 10px 20px 0 0;
        }

        .rptFormBox {
            border: 1px solid var(--stroke-e-3);
            border-radius: 8px;
            background: var(--white);
            padding: 20px;
        }

        .rptFormBox h2 {
            font-size: 18px;
            margin-bottom: 15px;
            color: var(--text-1);
        }

        .rptFormBox .form-label {
            font-size: 13px;
            margin-bottom: 5px;
        }

        .rptSubmitBtn {
            background: var(--primary, #0d6efd);
            color: #fff;
            padding: 8px 30px;
        }
    </style>
@endsection

@section('content')
    <section class="rptSec">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1>رپورتاژ آگهی</h1>

                    <div class="rptIntro">
                        <p>
                            رپورتاژ آگهی یکی از موثرترین روش‌های معرفی کسب‌وکار شما به مخاطبان گروه رسانه‌ای صبح ساحل است.
                            محتوای معرفی برند، محصول یا خدمات شما توسط تحریریه به شکل یک گزارش خبری حرفه‌ای تنظیم و در سایت منتشر می‌شود.
                        </p>
                        <ul>
                            <li>انتشار دائمی در پایگاه خبری با درج شفاف عنوان «رپورتاژ آگهی»</li>
                            <li>امکان درج لینک مستقیم به وب‌سایت یا صفحه شما</li>
                            <li>بازبینی و ویرایش نهایی متن توسط تحریریه</li>
                        </ul>
                        <p>
                            روند کار: پس از ثبت سفارش، همکاران ما آن را بررسی و قیمت را تعیین می‌کنند؛ سپس لینک پرداخت برای شما ارسال می‌شود
                            و پس از تایید پرداخت، رپورتاژ آماده‌سازی و منتشر خواهد شد.
                        </p>
                    </div>

                    <div class="rptFormBox">
                        <h2>ثبت سفارش رپورتاژ</h2>

                        @if (session('reportage_success'))
                            <div class="alert alert-success" role="alert">
                                {{ session('reportage_success') }}
                                @if (session('reportage_pay_url'))
                                    <br>
                                    لینک پیگیری و پرداخت سفارش شما (این لینک را نگه دارید):
                                    <a href="{{ session('reportage_pay_url') }}">{{ session('reportage_pay_url') }}</a>
                                @endif
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

                        <form method="POST" action="{{ route('website.rtl.reportage.store') }}" class="row g-3">
                            @csrf

                            {{-- Honeypot: hidden from real users --}}
                            <div style="position:absolute;right:-9999px;" aria-hidden="true">
                                <label for="rpt-website">وب‌سایت</label>
                                <input type="text" id="rpt-website" name="website" tabindex="-1" autocomplete="off">
                            </div>

                            <div class="col-md-4">
                                <label for="rpt-name" class="form-label">نام و نام خانوادگی *</label>
                                <input type="text" name="name" id="rpt-name" class="form-control" value="{{ old('name') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="rpt-mobile" class="form-label">شماره موبایل *</label>
                                <input type="text" name="mobile" id="rpt-mobile" class="form-control" dir="ltr" placeholder="09xxxxxxxxx" value="{{ old('mobile') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="rpt-email" class="form-label">ایمیل (اختیاری)</label>
                                <input type="email" name="email" id="rpt-email" class="form-control" value="{{ old('email') }}">
                            </div>
                            <div class="col-md-8">
                                <label for="rpt-subject" class="form-label">موضوع رپورتاژ *</label>
                                <input type="text" name="subject" id="rpt-subject" class="form-control" value="{{ old('subject') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="rpt-date" class="form-label">تاریخ انتشار پیشنهادی (اختیاری)</label>
                                <input type="date" name="desired_publish_date" id="rpt-date" class="form-control" value="{{ old('desired_publish_date') }}">
                            </div>
                            <div class="col-12">
                                <label for="rpt-brief" class="form-label">شرح سفارش (معرفی کسب‌وکار و نکات مورد نظر) *</label>
                                <textarea name="brief" id="rpt-brief" class="form-control" rows="5" required>{{ old('brief') }}</textarea>
                            </div>
                            <div class="col-md-8">
                                <label for="rpt-link" class="form-label">لینک وب‌سایت یا صفحه شما (اختیاری)</label>
                                <input type="url" name="link" id="rpt-link" class="form-control" dir="ltr" placeholder="https://..." value="{{ old('link') }}">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn transitionCls rptSubmitBtn">ثبت سفارش</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
