<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Adama Second-Hand Market') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-ink antialiased surface-auth {{ app()->getLocale() === 'am' ? 'font-ethiopic' : '' }}">
        <div class="relative min-h-screen overflow-hidden">
            <div class="pointer-events-none absolute inset-0 pattern-weave opacity-40"></div>

            <div class="relative mx-auto flex min-h-screen max-w-7xl flex-col px-4 py-6 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between gap-4">
                    <a href="{{ route('home') }}" class="transition opacity-95 hover:opacity-100">
                        <x-application-logo />
                    </a>
                </div>

                <div class="flex flex-1 flex-col items-center justify-center py-10 sm:py-14">
                    <div class="w-full max-w-md animate-fade-up rounded-panel border border-forest/10 bg-white/90 p-6 shadow-panel backdrop-blur-sm sm:p-8">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
