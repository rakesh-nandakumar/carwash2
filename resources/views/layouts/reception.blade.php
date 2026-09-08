<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'AutoCare Pro - Reception' }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/searchable-dropdown.css') }}">
</head>
<body>
    <div class="reception-fullscreen">
        @yield('content')
    </div>
    <script src="{{ asset('js/searchable-dropdown.js') }}"></script>
</body>
</html>
