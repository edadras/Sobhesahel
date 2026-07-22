@extends('website.rtl.partials.base')

@section('style')
    <link rel="stylesheet" href="{{ asset('asset/css/archive-rtl.css') }}">
    <style>
        .orgPgSec .orgCard {
            max-width: 760px;
            margin: 30px auto;
        }

        .orgPgSec .orgHead {
            text-align: center;
            margin-bottom: 20px;
        }

        .orgPgSec .orgHead .imgBx img {
            width: 120px;
            height: 120px;
            object-fit: contain;
            border-radius: 12px;
        }

        .orgPgSec .orgHead h1 {
            font-size: 24px;
            margin: 12px 0 4px;
        }

        .orgPgSec .orgBadge {
            display: inline-block;
            margin-top: 6px;
            padding: 3px 14px;
            border-radius: 20px;
            background: var(--main-color, #0f766e);
            color: #fff;
            font-size: 13px;
        }

        .orgPgSec .orgSection {
            margin-bottom: 22px;
        }

        .orgPgSec .orgSection strong {
            display: block;
            margin-bottom: 8px;
        }

        .orgPgSec .orgSection p {
            line-height: 2;
            margin: 0;
        }

        .orgPgSec .orgMeta {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            justify-content: center;
            margin-bottom: 22px;
        }

        .orgPgSec .orgMeta span {
            font-size: 14px;
            opacity: .85;
        }

        .orgPgSec .fotrSocial {
            display: flex;
            justify-content: center;
            gap: 12px;
            list-style: none;
            padding: 0;
            margin: 0 0 22px;
        }

        .orgPgSec .fotrSocial li a {
            font-size: 20px;
            color: var(--text-1);
        }
    </style>
@endsection

@section('content')
    <div class="orgPgSec">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="orgCard">
                        <div class="orgHead">
                            @if($logo)
                                <div class="imgBx">
                                    <img src="{{ $logo }}" alt="{{ $organization->name }}">
                                </div>
                            @endif
                            <h1>{{ $organization->name }}</h1>
                            @if(!empty($organization->field_of_activity))
                                <span class="orgBadge">{{ $organization->field_of_activity }}</span>
                            @endif
                        </div>

                        <div class="orgMeta">
                            @if(!empty($organization->parent_company))
                                <span>شرکت مادر: {{ $organization->parent_company }}</span>
                            @endif
                            @if(!empty($organization->employees_count))
                                <span>تعداد کارکنان: {{ number_format($organization->employees_count) }} نفر</span>
                            @endif
                            @if(!empty($organization->website))
                                <span>
                                    وب‌سایت:
                                    <a href="{{ $organization->website }}" target="_blank" rel="nofollow noopener">
                                        {{ $organization->website }}
                                    </a>
                                </span>
                            @endif
                        </div>

                        @if(!empty($organization->social_links))
                            <ul class="fotrSocial">
                                @foreach($organization->social_links as $social_link)
                                    @if(!empty($social_link['network']) && !empty($social_link['url']))
                                        @php
                                            $social_icon = [
                                                'instagram' => 'icon-Instagram',
                                                'telegram' => 'icon-Telegram',
                                                'x' => 'icon-Twitter-X-1',
                                                'linkedin' => 'icon-Linkedin',
                                                'whatsapp' => 'icon-Whatsapp',
                                            ][$social_link['network']] ?? null;
                                        @endphp
                                        @if($social_icon)
                                            <li>
                                                <a href="{{ $social_link['url'] }}" target="_blank" rel="nofollow noopener" class="transitionCls">
                                                    <span class="{{ $social_icon }}"></span>
                                                </a>
                                            </li>
                                        @endif
                                    @endif
                                @endforeach
                            </ul>
                        @endif

                        @if(!empty($organization->description))
                            <div class="orgSection">
                                <strong>معرفی</strong>
                                <p>{{ $organization->description }}</p>
                            </div>
                        @endif

                        @if(!empty($organization->products_services))
                            <div class="orgSection">
                                <strong>محصولات و خدمات</strong>
                                <p>{{ $organization->products_services }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
