{{--
    Generic image advertise banner.

    Usage:
        @include('website.components.advertise-banner', ['position' => 'home_body_advertise_image'])
        @include('website.components.advertise-banner', ['position' => 'home_body_advertise_image', 'container' => true])

    Renders nothing when no active advertise is assigned to the given position.
--}}
@php
    $banner_ad = \App\Models\Advertise::getImageAdvertise($position);
@endphp

@if($banner_ad != null)
    @once
        <style>
            .advBanner {
                width: 100%;
                margin: 15px 0;
                text-align: center;
            }

            .advBanner a {
                display: block;
            }

            .advBanner img {
                max-width: 100%;
                height: auto;
                border-radius: 8px;
            }
        </style>
    @endonce

    @if(!empty($container))
        <section class="advBannerSec">
            <div class="container">
                <div class="row">
                    <div class="col-12">
    @endif

    <div class="advBanner advBanner--{{ $position }}">
        <a href="{{ $banner_ad['url'] }}" target="_blank" rel="nofollow">
            <img src="{{ \Illuminate\Support\Facades\Storage::url($banner_ad['image']) }}"
                 alt="{{ $banner_ad['name'] }}"
                 loading="lazy"/>
        </a>
    </div>

    @if(!empty($container))
                    </div>
                </div>
            </div>
        </section>
    @endif
@endif
