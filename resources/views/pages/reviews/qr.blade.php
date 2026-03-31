<!doctype html>
<html class="no-js" lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title>Viva Med Private Portal - Scan QR</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="icon" href="{{ asset('favicon.png') }}" type="image/x-icon" />
        <link href="https://fonts.googleapis.com/css?family=Nunito+Sans:300,400,600,700,800" rel="stylesheet">

        <script src="{{ asset('js/app.js') }}"></script>

        <link rel="stylesheet" href="{{ asset('all.css') }}">
        <link rel="stylesheet" href="{{ asset('dist/css/theme.css') }}">
        <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
        <link rel="stylesheet" href="{{ asset('plugins/icon-kit/dist/css/iconkit.min.css') }}">
        <link rel="stylesheet" href="{{ asset('plugins/ionicons/dist/css/ionicons.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    </head>

    <body class="home-gradient-bg">
        <div class="container text-center">
            <div class="my-5">
                <h2>Scan to Leave a Review</h2>
                <p>Use your mobile device to scan the QR code and submit feedback.</p>
                <div class="my-4">{!! $qrCode !!}</div>
                <p><strong>Or visit:</strong> <a href="{{ $url }}" target="_blank">{{ $url }}</a></p>
            </div>
        </div>
        <script src="{{ asset('all.js') }}"></script>
    </body>
</html>
