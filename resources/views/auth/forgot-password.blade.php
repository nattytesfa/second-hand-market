<x-guest-layout>
    <div class="mb-6">
        <h1 class="font-display text-2xl font-bold text-ink">{{ __('passwords.send_code') }}</h1>
        <p class="mt-1 text-sm leading-relaxed text-stone">{{ __('passwords.instructions') }}</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.request') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="phone" :value="__('login.phone_number')" />
            <x-text-input id="phone" class="mt-1.5 block w-full" type="tel" name="phone" :value="old('phone')" required autofocus autocomplete="tel" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between gap-3 pt-1">
            <a class="text-sm font-medium text-forest-soft transition hover:text-forest" href="{{ route('login') }}">
                {{ __('passwords.back_to_login') }}
            </a>

            <x-primary-button>
                {{ __('passwords.send_code') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
