<div class="qaFormBox">
    <h2>ثبت پرسش جدید</h2>
    <p>
        پرسش خود را از طریق فرم زیر ثبت کنید. پرسش شما پس از بررسی توسط تحریریه منتشر
        و پاسخ کارشناسی آن در همین بخش قرار می‌گیرد.
    </p>

    @if (session('qa_success'))
        <div class="alert alert-success" role="alert">
            {{ session('qa_success') }}
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

    <form method="POST" action="{{ route('website.rtl.qa.store') }}" enctype="multipart/form-data" class="row g-3">
        @csrf

        {{-- Honeypot: hidden from real users --}}
        <div style="position:absolute;right:-9999px;" aria-hidden="true">
            <label for="qa-website">وب‌سایت</label>
            <input type="text" id="qa-website" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div class="col-md-4">
            <label for="qa-name" class="form-label">نام و نام خانوادگی *</label>
            <input type="text" name="name" id="qa-name" class="form-control" value="{{ old('name') }}" required>
        </div>
        <div class="col-md-4">
            <label for="qa-email" class="form-label">ایمیل (اختیاری)</label>
            <input type="email" name="email" id="qa-email" class="form-control" value="{{ old('email') }}">
        </div>
        <div class="col-md-4">
            <label for="qa-phone" class="form-label">شماره تماس (اختیاری)</label>
            <input type="text" name="phone" id="qa-phone" class="form-control" value="{{ old('phone') }}">
        </div>
        <div class="col-md-4">
            <label for="qa-category" class="form-label">دسته‌بندی *</label>
            <select name="category_id" id="qa-category" class="form-control" required>
                <option value="">انتخاب کنید...</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->title }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-8">
            <label for="qa-title" class="form-label">عنوان پرسش *</label>
            <input type="text" name="title" id="qa-title" class="form-control" value="{{ old('title') }}" required>
        </div>
        <div class="col-12">
            <label for="qa-body" class="form-label">متن پرسش *</label>
            <textarea name="body" id="qa-body" class="form-control" rows="4" required>{{ old('body') }}</textarea>
        </div>
        <div class="col-md-8">
            <label for="qa-attachment" class="form-label">فایل ضمیمه (اختیاری - حداکثر ۲ مگابایت - PDF/JPG/PNG)</label>
            <input type="file" name="attachment" id="qa-attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png">
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn transitionCls qaSubmitBtn">ثبت پرسش</button>
        </div>
    </form>
</div>
