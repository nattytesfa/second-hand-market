<x-guest-layout>
    <div class="mb-6">
        <h1 class="font-display text-2xl font-bold text-ink">{{ __('login.submit') }}</h1>
        <p class="mt-1 text-sm text-stone">{{ __('messages.tagline') }}</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="phone" :value="__('login.phone_number')" />
            <x-text-input id="phone" class="mt-1.5 block w-full" type="tel" name="phone" :value="old('phone')" required autofocus autocomplete="tel" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('login.password')" />
            <x-password-input id="password" class="mt-1.5 block w-full"
                            name="password"
                            required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between gap-3">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-forest/25 text-forest shadow-sm focus:ring-forest-soft/40" name="remember">
                <span class="ms-2 text-sm text-stone">{{ __('login.remember_me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-medium text-forest-soft transition hover:text-forest focus:outline-none focus-visible:underline" href="{{ route('password.request') }}">
                    {{ __('login.forgot_password') }}
                </a>
            @endif
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full">
                {{ __('login.submit') }}
            </x-primary-button>
        </div>

        @if (Route::has('register'))
            <p class="pt-1 text-center text-sm text-stone">
                <a class="font-semibold text-forest transition hover:text-forest-deep" href="{{ route('register') }}">
                    {{ __('registration.submit') }}
                </a>
            </p>
        @endif
    </form>
</x-guest-layout>
