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
    </style>
@endsection

@section('content')
    <section class="payPgSec">
        <div class="container">
            <div class="row">
                <div class="col-12 col-lg-8">
                    <h1>پرداخت سفارش رپورتاژ آگهی</h1>

                    <div class="payPgCard">
                        <div><strong>موضوع:</strong> {{ $order->subject }}</div>
                        <div><strong>سفارش‌دهنده:</strong> {{ $order->name }}</div>
                        <div>
                            <strong>وضعیت سفارش:</strong>
                            {{ \App\Models\ReportageOrder::statusOptions()[$order->status] ?? $order->status }}
                        </div>
                        @if ($order->price)
                            <div>
                                <strong>مبلغ:</strong>
                                {{ \App\Models\Payment::faNumber(number_format((int) $order->price)) }} {{ config('payments.currency', 'تومان') }}
                            </div>
                        @endif
                    </div>

                    <div class="payPgCard">
                        @if ($order->status === \App\Models\ReportageOrder::STATUS_NEW)
                            <div class="alert alert-info" role="alert">
                                سفارش شما در حال بررسی توسط همکاران ماست؛ پس از تعیین قیمت، همین صفحه برای پرداخت فعال می‌شود.
                            </div>
                        @elseif ($order->status === \App\Models\ReportageOrder::STATUS_REJECTED)
                            <div class="alert alert-danger" role="alert">
                                متاسفانه این سفارش پذیرفته نشد.
                                @if (filled($order->reject_reason))
                                    <br><strong>دلیل:</strong> {{ $order->reject_reason }}
                                @endif
                            </div>
                        @elseif (in_array($order->status, [\App\Models\ReportageOrder::STATUS_PAID, \App\Models\ReportageOrder::STATUS_PUBLISHED], true))
                            <div class="alert alert-success" role="alert">
                                پرداخت این سفارش تایید شده است.
                                @if ($order->status === \App\Models\ReportageOrder::STATUS_PUBLISHED)
                                    رپورتاژ شما منتشر شده است.
                                @else
                                    رپورتاژ شما در نوبت آماده‌سازی و انتشار قرار دارد.
                                @endif
                            </div>
                        @elseif ($payment !== null)
                            @include('website.rtl.components.payment-offline', [
                                'payment' => $payment,
                                'init' => $init,
                                'action' => route('website.rtl.reportage.pay.submit', ['token' => $order->token]),
                            ])
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
