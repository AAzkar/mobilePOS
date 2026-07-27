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
<body class="bg-slate-50 text-slate-900 antialiased">
    <div class="mx-auto flex min-h-screen max-w-lg flex-col pb-20">
        <header class="sticky top-0 z-10 flex items-center justify-between bg-teal-700 px-4 py-3 text-white shadow print:hidden">
            <h1 class="text-lg font-semibold">{{ $title ?? 'MobilePOS' }}</h1>
            @auth
                <div class="flex items-center gap-2 text-xs">
                    <span class="text-teal-100">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded bg-teal-800 px-2 py-1">Log out</button>
                    </form>
                </div>
            @endauth
        </header>

        @if (session('status'))
            <div class="mx-4 mt-3 rounded-lg bg-emerald-100 px-4 py-2 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <main class="flex-1 px-4 py-4">
            {{ $slot }}
        </main>
    </div>

    <div class="print:hidden">
        <x-bottom-nav />
    </div>

    @auth
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                if (window.MobilePOS) {
                    window.MobilePOS.initIdleLogout({{ (int) config('session.lifetime') }});
                }
            });
        </script>
    @endauth
</body>
</html>
