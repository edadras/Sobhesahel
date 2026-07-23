{{--
    Advertise bar styled like the breaking news (خبر فوری) strip.
    It is rendered right after the breaking news bar and sticks to it
    (sits directly next to the strip at the bottom of the viewport).

    Renders nothing when no active advertise is assigned to the position.
--}}
@php
    $breaking_bar_ad = \App\Models\Advertise::getImageAdvertise('breaking_bar_advertise_image');
@endphp

@if($breaking_bar_ad != null)
    <style>
        .advFloutBar {
            bottom: 0;
            left: 0;
            width: 100%;
            height: 52px;
            background: var(--red-1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            color: var(--white);
            padding: 1px 20px;
            z-index: 9;
            position: fixed;
            transition: bottom 0.3s;
        }

        /* When the breaking news strip is present, stack right next to it. */
        body:has(.floutNews) .advFloutBar {
            bottom: 52px;
        }

        .advFloutBar > div {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 5px;
        }

        .advFloutBar > div i {
            width: 13px;
            min-width: 13px;
            height: 13px;
            background: var(--white);
            border-radius: 50%;
        }

        .advFloutBar > div strong {
            font-family: "IRANSansWebFaBold";
            font-size: 24px;
        }

        .advFloutBar a {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--white);
            text-decoration: none;
            overflow: hidden;
        }

        .advFloutBar a img {
            height: 44px;
            width: auto;
            border-radius: 4px;
        }

        .advFloutBar a p {
            margin: 0;
            text-align: center;
            font-family: "IRANSansWebFaBold";
            font-size: 16px;
            max-height: 24px;
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
        }

        .advFloutBar .clsAdvFloutBar {
            font-size: 24px;
            color: var(--white);
            cursor: pointer;
        }

        @media (max-width: 767px) {
            .advFloutBar > div strong {
                font-size: 16px;
            }

            .advFloutBar a p {
                font-size: 13px;
            }
        }
    </style>

    <div class="advFloutBar">
        <div>
            <i></i>
            <strong>آگهی:</strong>
        </div>
        {{-- Anchor goes through the click-tracking route (ads/click/{id}). --}}
        <a href="{{ route('advertise_click', ['id' => $breaking_bar_ad['id']]) }}" target="_blank" rel="nofollow">
            <img src="{{ \Illuminate\Support\Facades\Storage::url($breaking_bar_ad['image']) }}"
                 alt="{{ $breaking_bar_ad['name'] }}"
                 loading="lazy"/>
            <p>{{ $breaking_bar_ad['name'] }}</p>
        </a>
        <span class="icon-Group-2168 clsAdvFloutBar" onclick="this.parentElement.remove()"></span>
    </div>
@endif
