@extends('website.rtl.partials.base')

@section('style')
    <style>
        .payPgSec {
            margin: 30px 0 50px;
        }

        .payPgSec h1 {
            font-size: 22px;
            color: var(--text-1);
            margin-bottom: 15px;
        }

        .payPgCard {
            border: 1px solid var(--stroke-e-3);
            border-radius: 8px;
            background: var(--white);
            padding: 20px;
            margin-bottom: 20px;
            font-size: 14px;
            color: var(--text-1);
            line-height: 2;
        }

        .payPgInfo h2 {
            font-size: 16px;
            margin: 10px 0;
        }

        .payPgTable {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .payPgTable th,
        .payPgTable td {
            border: 1px solid var(--stroke-e-3);
            padding: 8px 15px;
            font-size: 14px;
            text-align: right;
        }

        .payPgNote {
            font-size: 13px;
            color: var(--text-2, #666);
        }

        .payPgBtn {
            background: var(--primary, #0d6efd);
            color: #fff;
            padding: 8px 30px;
        }

        .payPgAccessCode {
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 3px;
            direction: ltr;
            display: inline-block;
            border: 1px dashed var(--stroke-e-3);
            border-radius: 6px;
            padding: 5px 20px;
            margin-top: 5px;
        }
    </style>
@endsection

@section('content')
    <section class="payPgSec">
        <div class="container">
            <div class="row">
                <div class="col-12 col-lg-8">
                    <h1>پرداخت اشتراک ویژه</h1>

                    <div class="payPgCard">
                        <div><strong>پلن:</strong> {{ $subscription->plan?->name }}</div>
                        <div>
                            <strong>مدت اعتبار:</strong>
                            {{ \App\Models\Payment::faNumber((string) ($subscription->plan?->duration_days ?? '')) }} روز
                        </div>
                        <div><strong>شماره موبایل:</strong> <span dir="ltr">{{ $subscription->mobile }}</span></div>
                        <div><strong>مبلغ:</strong> {{ $payment->formattedAmount() }}</div>
                    </div>

                    <div class="payPgCard">
                        @if ($subscription->isActive())
                            <div class="alert alert-success" role="alert">
                                اشتراک شما فعال است.
                                تاریخ انقضا: {{ \Morilog\Jalali\Jalalian::fromCarbon($subscription->ends_at)->format('%d %B %Y') }}
                            </div>
                            <div>
                                کد اشتراک شما (برای دانلود نسخه دیجیتال نشریات آن را نگه دارید):
                                <br>
                                <span class="payPgAccessCode">{{ $subscription->access_code }}</span>
                            </div>
                        @else
                            @include('website.rtl.components.payment-offline', [
                                'payment' => $payment,
                                'init' => $init,
                                'action' => route('website.rtl.subscribe.pay.submit', ['token' => $payment->token]),
                            ])
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
