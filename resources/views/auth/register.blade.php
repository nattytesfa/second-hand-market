<x-guest-layout>
    <div class="mb-6">
        <h1 class="font-display text-2xl font-bold text-ink">{{ __('registration.submit') }}</h1>
        <p class="mt-1 text-sm text-stone">{{ __('messages.cta_join') }}</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="name" :value="__('registration.name')" />
            <x-text-input id="name" class="mt-1.5 block w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="phone" :value="__('registration.phone_number')" />
            <x-text-input id="phone" class="mt-1.5 block w-full" type="tel" name="phone" :value="old('phone')" required autocomplete="tel" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('registration.password')" />
            <x-password-input id="password" class="mt-1.5 block w-full"
                            name="password"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('registration.confirm_password')" />
            <x-password-input id="password_confirmation" class="mt-1.5 block w-full"
                            name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full">
                {{ __('registration.submit') }}
            </x-primary-button>
        </div>

        <p class="pt-1 text-center text-sm text-stone">
            <a class="font-semibold text-forest transition hover:text-forest-deep" href="{{ route('login') }}">
                {{ __('registration.already_registered') }}
            </a>
        </p>
    </form>
</x-guest-layout>
