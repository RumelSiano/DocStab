<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#123f3b">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CAE CDTrack</title>
    <link rel="stylesheet" href="{{ asset('prototype/vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('prototype/prototype.css') }}">
    <link rel="stylesheet" href="{{ asset('app/portal.css') }}">
    <script src="{{ asset('prototype/vendor/bootstrap/bootstrap.bundle.min.js') }}" defer></script>
    <script src="{{ asset('app/portal.js') }}" type="module"></script>
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>
    <div id="app" data-api="{{ url('/api') }}" data-home="{{ route('portal') }}" data-logo="{{ asset('prototype/cae-emblem.jpg') }}" data-reset-token="{{ $resetToken ?? '' }}" data-reset-email="{{ $resetEmail ?? '' }}">
        <main id="main" class="loading-screen"><h1>{{ isset($resetToken) ? 'Reset your CDTrack password' : 'CAE CDTrack' }}</h1><p role="status">Opening your workspace…</p></main>
    </div>
    <div id="toast" role="status" aria-live="polite"></div>
    <noscript><p class="alert alert-warning">Enable JavaScript to use the CDTrack portal.</p></noscript>
</body>
</html>
