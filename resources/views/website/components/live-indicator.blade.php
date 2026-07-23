{{--
    Header live indicator (نشانگر پخش زنده).

    Self-contained component (market-prices strip pattern): queries LiveStream
    itself (cached, null-safe) and renders nothing unless a stream is live now.
--}}
@php
    $live_indicator_on = \App\Models\LiveStream::hasLiveNow();
@endphp

@if($live_indicator_on)
    <style>
        .liveIndBdg {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #d32f2f;
            color: #fff !important;
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 13px;
            text-decoration: none !important;
            white-space: nowrap;
        }

        .liveIndBdg:hover {
            color: #fff !important;
            background: #b71c1c;
        }

        .liveIndDot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #fff;
            animation: liveIndPulse 1.2s infinite;
        }

        @keyframes liveIndPulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
    </style>
    <a href="{{ route('website.rtl.live') }}" class="liveIndBdg transitionCls">
        <span class="liveIndDot"></span>
        پخش زنده
    </a>
@endif
