@extends('website.rtl.partials.base')

@section('style')
    <style>
        .subSec {
            margin: 30px 0 50px;
        }

        .subSec h1 {
            font-size: 22px;
            color: var(--text-1);
            margin-bottom: 15px;
        }

        .subIntro {
            border: 1px solid var(--stroke-e-3);
            border-radius: 8px;
            background: var(--white);
            padding: 20px;
            margin-bottom: 25px;
            line-height: 2;
            font-size: 14px;
            color: var(--text-1);
        }

        .subPlanCard {
            border: 1px solid var(--stroke-e-3);
            border-radius: 8px;
            background: var(--white);
            padding: 20px;
            height: 100%;
            display: flex;
            flex-direction: column;
            text-align: center;
        }

        .subPlanCard h3 {
            font-size: 17px;
            color: var(--text-1);
            margin-bottom: 10px;
        }

        .subPlanPrice {
            font-size: 20px;
            font-weight: bold;
            color: var(--primary, #0d6efd);
            margin-bottom: 5px;
        }

        .subPlanDuration {
            font-size: 13px;
            color: var(--text-2, #666);
            margin-bottom: 10px;
        }

        .subPlanCard ul {
            list-style: none;
            padding: 0;
            margin: 0 0 15px;
            font-size: 13px;
            color: var(--text-1);
            line-height: 2;
            flex-grow: 1;
        }

        .subPlanBtn,
        .subSubmitBtn {
            background: var(--primary, #0d6efd);
            color: #fff;
            padding: 8px 25px;
        }

        .subFormBox {
            border: 1px solid var(--stroke-e-3);
            border-radius: 8px;
            background: var(--white);
            padding: 20px;
            margin-top: 25px;
        }

        .subFormBox h2 {
            font-size: 18px;
            margin-bottom: 15px;
            color: var(--text-1);
        }
    </style>
@endsection

@section('content')
    <section class="subSec">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1>اشتراک ویژه</h1>

                    <div class="subIntro">
                        <p>
                            با تهیه اشتراک ویژه صبح ساحل، به نسخه دیجیتال نشریات و آرشیو PDF روزنامه دسترسی خواهید داشت.
                            پس از انتخاب پلن و تکمیل فرم، اطلاعات پرداخت نمایش داده می‌شود و به محض تایید پرداخت،
                            کد اشتراک برای شما صادر و اطلاع‌رسانی می‌شود.
                        </p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if ($plans->isEmpty())
                        <div class="alert alert-info" role="alert">
                            در حال حاضر پلن فعالی برای فروش اشتراک وجود ندارد؛ لطفا بعدا مراجعه کنید.
                        </div>
                    @else
                        <div class="row g-3">
                            @foreach ($plans as $plan)
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="subPlanCard">
                                        <h3>{{ $plan->name }}</h3>
                                        <div class="subPlanPrice">{{ $plan->formattedPrice() }}</div>
                                        <div class="subPlanDuration">
                                            مدت اعتبار: {{ \App\Models\Payment::faNumber((string) $plan->duration_days) }} روز
                                        </div>
                                        @if (!empty($plan->features))
                                            <ul>
                                                @foreach ((array) $plan->features as $feature)
                                                    <li>{{ $feature }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                        <div>
                                            <a href="#subscribe-form"
                                               class="btn transitionCls subPlanBtn"
                                               onclick="document.getElementById('sub-plan').value='{{ $plan->id }}';">
                                                انتخاب این پلن
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="subFormBox" id="subscribe-form">
                            <h2>ثبت سفارش اشتراک</h2>

                            <form method="POST" action="{{ route('website.rtl.subscribe.store') }}" class="row g-3">
                                @csrf

                                {{-- Honeypot: hidden from real users --}}
                                <div style="position:absolute;right:-9999px;" aria-hidden="true">
                                    <label for="sub-website">وب‌سایت</label>
                                    <input type="text" id="sub-website" name="website" tabindex="-1" autocomplete="off">
                                </div>

                                <div class="col-md-3">
                                    <label for="sub-plan" class="form-label">پلن اشتراک *</label>
                                    <select name="plan_id" id="sub-plan" class="form-control" required>
                                        <option value="">انتخاب کنید...</option>
                                        @foreach ($plans as $plan)
                                            <option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id)>
                                                {{ $plan->name }} — {{ $plan->formattedPrice() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="sub-name" class="form-label">نام و نام خانوادگی *</label>
                                    <input type="text" name="name" id="sub-name" class="form-control" value="{{ old('name') }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="sub-mobile" class="form-label">شماره موبایل *</label>
                                    <input type="text" name="mobile" id="sub-mobile" class="form-control" dir="ltr" placeholder="09xxxxxxxxx" value="{{ old('mobile') }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="sub-email" class="form-label">ایمیل (اختیاری)</label>
                                    <input type="email" name="email" id="sub-email" class="form-control" value="{{ old('email') }}">
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn transitionCls subSubmitBtn">ادامه و پرداخت</button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
