@include('website.rtl.partials.head')
@yield('style')
<style>
    #breaking-news-text {
        color: inherit !important;
        text-decoration: none !important;
    }

    #breaking-news-link {
        color: inherit !important;
        text-decoration: none !important;
    }

    #breaking-news-text:visited,
    #breaking-news-text:hover,
    #breaking-news-text:active {
        color: inherit !important;
    }

    #breaking-news-link:visited,
    #breaking-news-link:hover,
    #breaking-news-link:active {
        color: inherit !important;
    }

    .text-decoration-none{
        text-decoration: none !important;
        color: var(--text-1);
    }

    .text-decoration-none:hover{
        text-decoration: none !important;
        color: var(--text-1);
    }
</style>
{{--@livewireStyles--}}
</head>

<body>
@include('website.rtl.partials.header')

@include('website.components.yektanet-ads', ['position' => 'header'])

@include('website.components.yektanet-ads', ['position' => 'content_top'])

@yield('content')

@include('website.components.yektanet-ads', ['position' => 'content_bottom'])
@livewire('breaking-news')
@include('website.components.advertise-breaking-bar')

@include('website.components.yektanet-ads', ['position' => 'footer'])

@include('website.rtl.partials.footer')

@include('website.components.yektanet-ads', ['position' => 'body_end'])
@yield('script')
{{--@livewireScripts--}}

{{--<script>--}}
{{--    var _paq = window._paq = window._paq || [];--}}
{{--    /* tracker methods like "setCustomDimension" should be called before "trackPageView" */--}}
{{--    _paq.push(['trackPageView']);--}}
{{--    _paq.push(['enableLinkTracking']);--}}
{{--    (function() {--}}
{{--        var u="//matomo.develogist.com/";--}}
{{--        _paq.push(['setTrackerUrl', u+'matomo.php']);--}}
{{--        _paq.push(['setSiteId', '1']);--}}
{{--        var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];--}}
{{--        g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);--}}
{{--    })();--}}
{{--</script>--}}
</body>
</html>
