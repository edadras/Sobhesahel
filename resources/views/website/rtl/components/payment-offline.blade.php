{{--
    Shared offline-payment block (کارت به کارت).
    Expects: $payment (App\Models\Payment), $init (driver create() result), $action (submit URL)
--}}
@if (session('payment_message'))
    <div class="alert alert-info" role="alert">
        {{ session('payment_message') }}
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

@if ($payment->status === \App\Models\Payment::STATUS_PAID)
    <div class="alert alert-success" role="alert">
        پرداخت شما به مبلغ {{ $payment->formattedAmount() }} تایید شده است. با سپاس از اعتماد شما.
    </div>
@elseif ($payment->status === \App\Models\Payment::STATUS_MANUAL_REVIEW)
    <div class="alert alert-warning" role="alert">
        کد رهگیری شما ({{ $payment->ref_code }}) ثبت شده و پرداخت به مبلغ {{ $payment->formattedAmount() }}
        در انتظار بررسی همکاران ماست. نتیجه از طریق تماس یا پیامک اطلاع‌رسانی می‌شود.
    </div>
@elseif ($init !== null && ($init['type'] ?? '') === 'redirect')
    <a href="{{ $init['url'] }}" class="btn transitionCls payPgBtn">پرداخت آنلاین</a>
@elseif ($init !== null)
    <div class="payPgInfo">
        <h2>اطلاعات واریز (کارت به کارت)</h2>
        <table class="payPgTable">
            <tbody>
            @foreach (($init['instructions'] ?? []) as $label => $value)
                <tr>
                    <th>{{ $label }}</th>
                    <td dir="ltr">{{ $value }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @if (filled($init['note'] ?? null))
            <p class="payPgNote">{{ $init['note'] }}</p>
        @endif
    </div>

    <form method="POST" action="{{ $action }}" class="row g-3 payPgForm">
        @csrf
        <div class="col-md-8">
            <label for="pay-ref-code" class="form-label">کد رهگیری واریز *</label>
            <input type="text" name="ref_code" id="pay-ref-code" class="form-control" dir="ltr" value="{{ old('ref_code') }}" required>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn transitionCls payPgBtn">ثبت کد رهگیری</button>
        </div>
    </form>
@endif
