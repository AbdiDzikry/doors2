<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#089244">

        <title>{{ config('app.name', 'Doors Tablet') }}</title>

        <!-- PWA Manifest -->
        <link rel="manifest" href="/build/manifest.webmanifest">
        <link rel="apple-touch-icon" href="/images/pwa-icon-192x192.png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50 overflow-hidden">
        <div class="h-screen w-screen relative">
            {{ $slot }}
        </div>
        @stack('scripts')
    </body>
</html>
