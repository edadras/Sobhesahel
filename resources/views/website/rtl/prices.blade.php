@extends('website.rtl.partials.base')

@section('style')
    <style>
        .prcPgSec {
            margin: 30px 0 50px;
        }

        .prcPgSec h1 {
            font-size: 22px;
            color: var(--text-1);
            margin-bottom: 20px;
        }

        .prcPgGroup {
            margin-bottom: 30px;
        }

        .prcPgGroup .homPgTitle {
            margin-bottom: 12px;
        }

        .prcPgTableWrap {
            border: 1px solid var(--stroke-e-3);
            border-radius: 8px;
            background: var(--white);
            overflow-x: auto;
        }

        .prcPgTable {
            width: 100%;
            border-collapse: collapse;
            min-width: 550px;
        }

        .prcPgTable th,
        .prcPgTable td {
            padding: 10px 15px;
            text-align: right;
            font-size: 14px;
            border-bottom: 1px solid var(--stroke-e-3);
            white-space: nowrap;
        }

        .prcPgTable tr:last-child td {
            border-bottom: none;
        }

        .prcPgTable th {
            color: var(--text-2);
            font-weight: normal;
            font-size: 13px;
            background: var(--body-bg);
        }

        .prcPgTable td strong {
            color: var(--text-1);
        }

        .prcPgChng {
            direction: ltr;
            display: inline-block;
        }

        .prcPgUp {
            color: #18a35a;
        }

        .prcPgDown {
            color: var(--red-1);
        }

        .prcPgFlat {
            color: var(--text-2);
        }

        .prcPgDate {
            font-size: 12px;
            color: var(--text-2);
        }

        .prcPgEmpty {
            border: 1px solid var(--stroke-e-3);
            border-radius: 8px;
            background: var(--white);
            padding: 40px 20px;
            text-align: center;
            color: var(--text-2);
        }
    </style>
@endsection

@section('content')
    <section class="prcPgSec">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1>قیمت‌ها</h1>

                    @forelse($groups as $category => $items)
                        <div class="prcPgGroup">
                            <div class="homPgTitle">
                                <span></span>
                                <strong>{{ \App\Models\MarketPrice::CATEGORIES[$category] ?? $category }}</strong>
                            </div>
                            <div class="prcPgTableWrap">
                                <table class="prcPgTable">
                                    <thead>
                                    <tr>
                                        <th>عنوان</th>
                                        <th>قیمت</th>
                                        <th>تغییر</th>
                                        <th>آخرین بروزرسانی</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($items as $item)
                                        <tr>
                                            <td><strong>{{ $item->name }}</strong></td>
                                            <td>
                                                <strong>{{ $item->formatted_price }}</strong>
                                                <small>{{ $item->unit }}</small>
                                            </td>
                                            <td>
                                                @if($item->formatted_change_percent !== null)
                                                    <span class="prcPgChng {{ $item->trend > 0 ? 'prcPgUp' : ($item->trend < 0 ? 'prcPgDown' : 'prcPgFlat') }}">
                                                        @if($item->trend > 0)
                                                            &#9650;
                                                        @elseif($item->trend < 0)
                                                            &#9660;
                                                        @endif
                                                        {{ $item->formatted_change_percent }}
                                                    </span>
                                                @else
                                                    <span class="prcPgFlat">-</span>
                                                @endif
                                            </td>
                                            <td class="prcPgDate">
                                                @if($item->fetched_at)
                                                    {{ \App\Models\MarketPrice::toFarsiNumber(\Morilog\Jalali\Jalalian::fromCarbon($item->fetched_at)->format('Y/m/d H:i')) }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @empty
                        <div class="prcPgEmpty">
                            در حال حاضر قیمتی برای نمایش ثبت نشده است.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
@endsection
