<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium uppercase tracking-wider text-forest-soft">{{ __('navigation.dashboard') }}</p>
            <h2 class="mt-1 font-display text-2xl font-bold leading-tight text-ink">
                {{ __('messages.dashboard_welcome') }}, {{ Auth::user()->name }}
            </h2>
        </div>
    </x-slot>

    <div class="py-10 sm:py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="animate-fade-up overflow-hidden rounded-panel border border-forest/10 bg-white/90 p-6 shadow-soft sm:p-8">
                <p class="text-base text-ink">{{ __('messages.logged_in') }}</p>
                <p class="mt-2 text-sm leading-relaxed text-stone">{{ __('messages.dashboard_hint') }}</p>
            </div>
        </div>
    </div>
</x-app-layout>
