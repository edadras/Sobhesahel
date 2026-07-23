<!DOCTYPE html>
<html lang="fa" dir="rtl" data-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>@yield('title', 'پنل اعضا') | صبح ساحل</title>
<link rel="stylesheet" href="{{ asset('asset/member/styles.css') }}">
{{-- Apply the saved theme before first paint to avoid a light-mode flash. --}}
<script>try{document.documentElement.setAttribute('data-theme',localStorage.getItem('ss-theme')||'light')}catch(e){}</script>
@stack('styles')
</head>
<body @if(session('status')) data-flash="{{ session('status') }}" @endif>
@include('member.partials.header')

<div class="app">
    @include('member.partials.sidebar', ['active' => $active ?? ''])

    <div class="main">
        @if ($errors->any())
            <div class="alert alert-error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>
</div>

@include('member.partials.footer')

<script src="{{ asset('asset/member/member.js') }}"></script>
@stack('scripts')
</body>
</html>
