<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="robots" content="noindex,nofollow">
    <title>{{ ($page_title ?? null) ? $page_title . ' - ' : '' }}Coming Soon - Sobhe Sahel</title>
    <link rel="icon" href="{{ asset('asset/img/logo.png') }}"/>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Tahoma, Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5f6f8;
            color: #23282d;
        }
        .box {
            text-align: center;
            padding: 48px 32px;
            max-width: 480px;
        }
        .box img { width: 120px; margin-bottom: 24px; }
        .box h1 { font-size: 26px; margin-bottom: 12px; }
        .box p { font-size: 15px; line-height: 1.8; color: #5a6068; margin-bottom: 24px; }
        .box a {
            display: inline-block;
            padding: 10px 24px;
            border-radius: 6px;
            background: #0073aa;
            color: #fff;
            text-decoration: none;
            font-size: 14px;
        }
        .box a:hover { background: #005d8f; }
    </style>
</head>
<body>
<div class="box">
    <img src="{{ asset('asset/img/logo.svg') }}" alt="Sobhe Sahel"/>
    <h1>{{ ($page_title ?? null) ?: 'This page is coming soon' }}</h1>
    <p>The English edition of this section is not available yet. We are working on it — please check back soon.</p>
    <a href="{{ \Illuminate\Support\Facades\Route::has('website.ltr.home') ? route('website.ltr.home') : route('website.home') }}">Back to home</a>
</div>
</body>
</html>
