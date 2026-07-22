@include('website.ltr.partials.head')
@yield('style')
</head>

<body>
@include('website.ltr.partials.header')

@include('website.components.yektanet-ads', ['position' => 'header'])

@include('website.components.yektanet-ads', ['position' => 'content_top'])

@yield('content')

@include('website.components.yektanet-ads', ['position' => 'content_bottom'])

@include('website.components.yektanet-ads', ['position' => 'footer'])

@include('website.ltr.partials.footer')

@include('website.components.yektanet-ads', ['position' => 'body_end'])
@yield('script')
</body>
</html>
