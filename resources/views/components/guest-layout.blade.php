@props(['title' => null])
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0f766e">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? "{$title} — MobilePOS" : 'MobilePOS' }}</title>

    <link rel="manifest" href="/manifest.json">
    <link rel="icon" href="/icons/icon-192.png">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-50 px-4 text-slate-900 antialiased">
    <div class="w-full max-w-sm">
        {{ $slot }}
    </div>
</body>
</html>
