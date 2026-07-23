<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SobheSahel Archive</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
        }

        #app {
            height: 100%;
            width: 100%;
        }
    </style>
</head>

<body>
<div id="app">
    <vue-pdf-app style="height: 100vh;" pdf="{{ $url }}"></vue-pdf-app>
</div>
@php $attached_news = ($post ?? null)?->news ?? collect(); @endphp
@if($attached_news->isNotEmpty())
    <div dir="rtl" style="padding: 16px; background: #fff; font-family: Tahoma, sans-serif;">
        <h2 style="font-size: 16px; margin: 0 0 12px;">خبرهای این شماره</h2>
        <ul style="list-style: none; margin: 0; padding: 0;">
            @foreach($attached_news as $news_item)
                <li style="padding: 6px 0; border-bottom: 1px solid #eee;">
                    <a href="{{ $news_item->getUrl() }}" style="color: #0d6efd; text-decoration: none;">
                        {{ $news_item->title }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endif
@vite(['resources/js/pdf.js'])
</body>
</html>
