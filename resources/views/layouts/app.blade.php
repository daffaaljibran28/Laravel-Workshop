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
    <body class="font-sans antialiased" data-auth-user-id="{{ auth()->id() }}">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        @auth
        <div
            id="order-notification"
            class="fixed right-4 top-4 z-50 hidden max-w-sm rounded-lg border border-green-200 bg-white p-4 text-gray-900 shadow-lg"
            role="status"
            aria-live="polite"
            aria-atomic="true"
        >
            <div class="flex items-start gap-3">
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-green-700">Transaksi berhasil</p>
                    <p id="order-notification-message" class="mt-1 text-sm text-gray-600"></p>
                </div>
                <button
                    id="dismiss-order-notification"
                    type="button"
                    class="rounded px-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                    aria-label="Tutup notifikasi"
                >
                    &times;
                </button>
            </div>
        </div>
        @endauth
    </body>
</html>