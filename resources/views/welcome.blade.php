<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="{{ __('messages.tagline') }}">

        <title>{{ config('app.name', 'Adama Second-Hand Market') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50 text-gray-900 {{ app()->getLocale() === 'am' ? 'font-ethiopic' : '' }}">
        <div class="min-h-screen flex flex-col">
            <header class="w-full border-b border-gray-200 bg-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
                    <span class="text-lg font-semibold text-gray-800">
                        {{ config('app.name', 'Adama Second-Hand Market') }}
                    </span>

                    <div class="flex items-center gap-3">
                        <x-locale-switcher />

                        @if (Route::has('login'))
                            <nav class="flex items-center gap-4 text-sm">
                                @auth
                                    <a href="{{ url('/dashboard') }}" class="text-gray-600 hover:text-gray-900">{{ __('navigation.dashboard') }}</a>
                                @else
                                    <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900">{{ __('login.submit') }}</a>
                                    @if (Route::has('register'))
                                        <a href="{{ route('register') }}" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">{{ __('registration.submit') }}</a>
                                    @endif
                                @endauth
                            </nav>
                        @endif
                    </div>
                </div>
            </header>

            <main class="flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-8 py-16">
                <div class="max-w-2xl w-full text-center">
                    <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900">
                        {{ config('app.name', 'Adama Second-Hand Market') }}
                    </h1>
                    <p class="mt-4 text-base sm:text-lg text-gray-600">
                        {{ __('messages.tagline') }}
                    </p>
                    <p class="mt-2 text-sm text-gray-500">
                        {{ __('messages.setup_notice') }}
                    </p>
                </div>
            </main>

            <footer class="border-t border-gray-200 bg-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-sm text-gray-500 text-center">
                    &copy; {{ date('Y') }} {{ config('app.name', 'Adama Second-Hand Market') }}.
                </div>
            </footer>
        </div>
    </body>
</html>
