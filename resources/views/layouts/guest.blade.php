<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="relative flex min-h-screen flex-col items-center overflow-hidden bg-slate-950 px-4 pt-10 sm:justify-center sm:pt-0">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute left-1/2 top-0 h-96 w-96 -translate-x-1/2 rounded-full bg-slate-700/30 blur-3xl"></div>
                <div class="absolute -bottom-32 -right-32 h-80 w-80 rounded-full bg-slate-800/70 blur-3xl"></div>
            </div>

            <div class="relative z-10">
                <a href="/" class="flex flex-col items-center gap-3">
                    <x-application-logo class="h-16 w-auto sm:h-20" />
                    <span class="text-sm font-black uppercase tracking-[0.3em] text-slate-300">UCSD Rankings</span>
                </a>
            </div>

            <div class="guest-card relative z-10 mt-6 w-full overflow-hidden rounded-3xl border border-slate-700 bg-slate-900/90 px-6 py-7 text-slate-100 shadow-2xl shadow-black/30 backdrop-blur sm:max-w-md sm:px-8 sm:py-8">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
