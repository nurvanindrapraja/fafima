<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('icon_fafima_small.png') }}">
        
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center py-10 sm:py-16 bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900">
            <div class="flex items-center justify-center gap-3">
                <a href="/" class="flex-shrink-0">
                    <img src="{{ asset('icon_fafima_small.png') }}" alt="Fafima Logo" class="w-10 h-10 drop-shadow-[0_0_15px_rgba(59,130,246,0.5)]">
                </a>
                <h1 class="text-3xl font-bold tracking-wider m-0 leading-none pt-1"><span class="text-white">FA</span><span class="text-blue-400">FIMA</span></h1>
            </div>

            <div class="w-full {{ $maxWidth ?? 'sm:max-w-md' }} mt-8 px-6 py-8 bg-slate-800/40 backdrop-blur-xl border border-slate-700/50 shadow-2xl overflow-hidden sm:rounded-2xl">
                {{ $slot }}
            </div>
        </div>
        @livewireScripts
    </body>
</html>
