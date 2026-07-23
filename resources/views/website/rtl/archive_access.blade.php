@extends('website.rtl.partials.base')

@section('style')
    <style>
        .arcAccSec {
            margin: 40px 0 60px;
        }

        .arcAccBox {
            border: 1px solid var(--stroke-e-3);
            border-radius: 8px;
            background: var(--white);
            padding: 25px;
            max-width: 550px;
            margin: 0 auto;
            font-size: 14px;
            color: var(--text-1);
            line-height: 2;
        }

        .arcAccBox h1 {
            font-size: 20px;
            color: var(--text-1);
            margin-bottom: 10px;
        }

        .arcAccBtn {
            background: var(--primary, #0d6efd);
            color: #fff;
            padding: 8px 30px;
        }
    </style>
@endsection

@section('content')
    <section class="arcAccSec">
        <div class="container">
            <div class="arcAccBox">
                <h1>نسخه دیجیتال نشریات</h1>
                <p>
                    دسترسی به نسخه PDF «{{ $post->title ?? 'این شماره' }}» ویژه دارندگان اشتراک دیجیتال است.
                    اگر اشتراک فعال دارید، کد اشتراک خود را وارد کنید؛ در غیر این صورت می‌توانید از
                    <a href="{{ route('website.rtl.subscribe') }}">صفحه اشتراک ویژه</a> اشتراک تهیه کنید.
                </p>

                @if (session('archive_access_error'))
                    <div class="alert alert-danger" role="alert">
                        {{ session('archive_access_error') }}
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

                <form method="POST" action="{{ route('website.rtl.archive.access') }}" class="row g-3">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                    <div class="col-md-8">
                        <label for="arc-access-code" class="form-label">کد اشتراک *</label>
                        <input type="text" name="access_code" id="arc-access-code" class="form-control" dir="ltr" value="{{ old('access_code') }}" required>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn transitionCls arcAccBtn">تایید کد</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
