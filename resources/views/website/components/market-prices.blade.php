{{--
    Market prices strip (قیمت لحظه‌ای ارز، طلا و بازارها).

    Self-contained component: queries active MarketPrice rows itself (cached)
    and renders nothing when no active price exists. Included on the home page
    and links to the full /prices page.
--}}
@php
    $market_price_groups = \App\Models\MarketPrice::activeGrouped();
@endphp

@if($market_price_groups->isNotEmpty())
    <style>
        .mrktPrcSec {
            margin: 25px 0;
        }

        .mrktPrcBox {
            border: 1px solid var(--stroke-e-3);
            border-radius: 8px;
            padding: 12px 15px;
            background: var(--white);
        }

        .mrktPrcHead {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 10px;
        }

        .mrktPrcHead .homPgTitle {
            margin: 0;
        }

        .mrktPrcAll {
            font-size: 13px;
            color: var(--red-1);
            text-decoration: none;
            white-space: nowrap;
        }

        .mrktPrcAll:hover {
            color: var(--red-1);
            text-decoration: none;
        }

        .mrktPrcList {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 5px;
        }

        .mrktPrcItem {
            flex: 0 0 auto;
            min-width: 150px;
            border: 1px solid var(--stroke-e-3);
            border-radius: 6px;
            padding: 8px 12px;
            background: var(--body-bg);
        }

        .mrktPrcItem .mrktPrcName {
            display: block;
            font-size: 13px;
            color: var(--text-2);
            margin-bottom: 4px;
            white-space: nowrap;
        }

        .mrktPrcItem .mrktPrcVal {
            display: flex;
            align-items: baseline;
            gap: 5px;
            white-space: nowrap;
        }

        .mrktPrcItem .mrktPrcVal strong {
            font-size: 15px;
            color: var(--text-1);
        }

        .mrktPrcItem .mrktPrcVal small {
            font-size: 11px;
            color: var(--text-2);
        }

        .mrktPrcChng {
            display: inline-block;
            margin-top: 4px;
            font-size: 12px;
            direction: ltr;
        }

        .mrktPrcUp {
            color: #18a35a;
        }

        .mrktPrcDown {
            color: var(--red-1);
        }

        .mrktPrcFlat {
            color: var(--text-2);
        }
    </style>

    <section class="mrktPrcSec">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="mrktPrcBox">
                        <div class="mrktPrcHead">
                            <div class="homPgTitle">
                                <span></span>
                                <strong>قیمت‌ها</strong>
                            </div>
                            <a href="{{ route('website.rtl.prices') }}" class="mrktPrcAll transitionCls">
                                مشاهده همه قیمت‌ها
                            </a>
                        </div>
                        <div class="mrktPrcList">
                            @foreach($market_price_groups as $market_price_items)
                                @foreach($market_price_items as $market_price)
                                    <div class="mrktPrcItem">
                                        <span class="mrktPrcName">{{ $market_price->name }}</span>
                                        <div class="mrktPrcVal">
                                            <strong>{{ $market_price->formatted_price }}</strong>
                                            <small>{{ $market_price->unit }}</small>
                                        </div>
                                        @if($market_price->formatted_change_percent !== null)
                                            <span class="mrktPrcChng {{ $market_price->trend > 0 ? 'mrktPrcUp' : ($market_price->trend < 0 ? 'mrktPrcDown' : 'mrktPrcFlat') }}">
                                                @if($market_price->trend > 0)
                                                    &#9650;
                                                @elseif($market_price->trend < 0)
                                                    &#9660;
                                                @endif
                                                {{ $market_price->formatted_change_percent }}
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
