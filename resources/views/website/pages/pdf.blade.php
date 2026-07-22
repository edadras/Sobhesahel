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
@vite(['resources/js/pdf.js'])
</body>
</html>
