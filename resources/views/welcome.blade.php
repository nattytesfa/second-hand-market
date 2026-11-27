<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="{{ __('messages.tagline') }}">

        <title>{{ config('app.name', 'Adama Second-Hand Market') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-ink {{ app()->getLocale() === 'am' ? 'font-ethiopic' : '' }}">
        <div class="min-h-screen flex flex-col">
            {{-- Transparent nav over full-bleed hero --}}
            <header class="absolute inset-x-0 top-0 z-20">
                <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8 sm:h-20">
                    <a href="{{ route('home') }}" class="animate-fade-in">
                        <x-application-logo variant="light" class="[&_span]:text-base sm:[&_span]:text-lg" />
                    </a>

                    <div class="flex items-center gap-2 sm:gap-4 animate-fade-in delay-1">
                        @guest
                            <x-locale-switcher variant="hero" />
                        @endguest

                        @if (Route::has('login'))
                            <nav class="flex items-center gap-2 sm:gap-3 text-sm">
                                @auth
                                    <a href="{{ url('/dashboard') }}" class="rounded-lg px-3 py-2 font-medium text-white/90 transition hover:bg-white/10 hover:text-white">
                                        {{ __('navigation.dashboard') }}
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="hidden rounded-lg px-3 py-2 font-medium text-white/90 transition hover:bg-white/10 hover:text-white sm:inline-flex">
                                        {{ __('login.submit') }}
                                    </a>
                                    @if (Route::has('register'))
                                        <a href="{{ route('register') }}" class="rounded-lg bg-saffron px-3.5 py-2 font-semibold text-forest-deep shadow-soft transition hover:bg-saffron-bright focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70">
                                            {{ __('registration.submit') }}
                                        </a>
                                    @endif
                                @endauth
                            </nav>
                        @endif
                    </div>
                </div>
            </header>

            {{-- Full-bleed hero: one composition --}}
            <section class="relative isolate flex min-h-[100svh] flex-col justify-end overflow-hidden">
                <div class="absolute inset-0 -z-10">
                    <img
                        src="https://images.unsplash.com/photo-1555529902-5261145633bf?auto=format&fit=crop&w=2400&q=80"
                        alt=""
                        class="animate-drift h-full w-full object-cover"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-forest-deep via-forest/75 to-forest/40"></div>
                    <div class="absolute inset-0 pattern-weave opacity-30 mix-blend-soft-light"></div>
                </div>

                <div class="mx-auto w-full max-w-7xl px-4 pb-16 pt-28 sm:px-6 sm:pb-24 sm:pt-32 lg:px-8 lg:pb-28">
                    <p class="animate-fade-up font-display text-sm font-semibold uppercase tracking-[0.22em] text-saffron-bright sm:text-base">
                        {{ config('app.name', 'Adama Second-Hand Market') }}
                    </p>
                    <h1 class="animate-fade-up delay-1 mt-4 max-w-3xl font-display text-4xl font-extrabold leading-[1.08] tracking-tight text-white sm:text-5xl lg:text-6xl">
                        {{ __('messages.hero_headline') }}
                    </h1>
                    <p class="animate-fade-up delay-2 mt-5 max-w-xl text-base leading-relaxed text-white/85 sm:text-lg">
                        {{ __('messages.tagline') }}
                    </p>
                    <div class="animate-fade-up delay-3 mt-8 flex flex-wrap items-center gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-xl bg-saffron px-6 py-3.5 text-sm font-semibold text-forest-deep shadow-soft transition hover:bg-saffron-bright focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70">
                                {{ __('navigation.dashboard') }}
                            </a>
                        @else
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-xl bg-saffron px-6 py-3.5 text-sm font-semibold text-forest-deep shadow-soft transition hover:bg-saffron-bright focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70">
                                    {{ __('messages.cta_join') }}
                                </a>
                            @endif
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-xl border border-white/35 bg-white/10 px-6 py-3.5 text-sm font-semibold text-white backdrop-blur-sm transition hover:bg-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70">
                                {{ __('login.submit') }}
                            </a>
                        @endauth
                    </div>
                </div>
            </section>

            {{-- Single-purpose below-fold section --}}
            <section class="surface-mist relative border-t border-forest/10">
                <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8">
                    <div class="max-w-2xl">
                        <h2 class="font-display text-2xl font-bold tracking-tight text-ink sm:text-3xl">
                            {{ __('messages.coming_title') }}
                        </h2>
                        <p class="mt-3 text-base leading-relaxed text-stone sm:text-lg">
                            {{ __('messages.setup_notice') }}
                        </p>
                    </div>

                    <ul class="mt-12 grid gap-10 sm:grid-cols-3">
                        <li>
                            <span class="font-display text-sm font-bold uppercase tracking-widest text-forest-soft">01</span>
                            <h3 class="mt-2 font-display text-lg font-bold text-ink">{{ __('messages.step_browse') }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-stone">{{ __('messages.step_browse_body') }}</p>
                        </li>
                        <li>
                            <span class="font-display text-sm font-bold uppercase tracking-widest text-forest-soft">02</span>
                            <h3 class="mt-2 font-display text-lg font-bold text-ink">{{ __('messages.step_sell') }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-stone">{{ __('messages.step_sell_body') }}</p>
                        </li>
                        <li>
                            <span class="font-display text-sm font-bold uppercase tracking-widest text-forest-soft">03</span>
                            <h3 class="mt-2 font-display text-lg font-bold text-ink">{{ __('messages.step_connect') }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-stone">{{ __('messages.step_connect_body') }}</p>
                        </li>
                    </ul>
                </div>
            </section>

            <footer class="border-t border-forest/10 bg-forest-deep text-white/70">
                <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-3 px-4 py-8 text-sm sm:flex-row sm:px-6 lg:px-8">
                    <span class="font-display font-semibold text-white/90">{{ config('app.name', 'Adama Second-Hand Market') }}</span>
                    <p>&copy; {{ date('Y') }} · Adama, Ethiopia</p>
                </div>
            </footer>
        </div>
    </body>
</html>
