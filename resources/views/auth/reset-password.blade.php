@php
    $maskedPhone = preg_replace('/(\d{2})\d{4}(\d{4})/', '$1****$2', $phone);
@endphp

<x-guest-layout>
    <div class="text-center">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-forest text-white shadow-soft">
            <svg class="h-7 w-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
        </div>

        <h1 class="mt-5 font-display text-2xl font-bold text-ink">{{ __('passwords.submit') }}</h1>
        <p class="mt-2 text-sm leading-relaxed text-stone">
            {{ __('passwords.reset_instructions') }}
        </p>
        <p class="mt-3 inline-flex items-center gap-2 rounded-full bg-mist px-3.5 py-1.5 text-sm font-semibold text-forest">
            <span class="h-1.5 w-1.5 rounded-full bg-forest-soft"></span>
            {{ $maskedPhone }}
        </p>
    </div>

    <x-auth-session-status class="mt-6" :status="session('status')" />

    <form method="POST" action="{{ route('password.reset') }}" class="mt-6 space-y-5">
        @csrf

        <div>
            <x-input-label for="code" :value="__('passwords.code_label')" class="mb-2.5 text-center" />
            <x-otp-input name="code" :value="old('code')" />
            <x-input-error :messages="$errors->get('code')" class="mt-3 text-center" />
        </div>

        <div>
            <x-input-label for="password" :value="__('passwords.new_password')" />
            <x-password-input id="password" class="mt-1.5 block w-full" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('passwords.confirm_new_password')" />
            <x-password-input id="password_confirmation" class="mt-1.5 block w-full" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="w-full py-3 text-base">
            {{ __('passwords.submit') }}
        </x-primary-button>
    </form>

    <div class="mt-5 text-center">
        <form method="POST" action="{{ route('password.resend') }}">
            @csrf
            <button type="submit" class="text-sm font-semibold text-forest transition hover:text-forest-deep focus:outline-none focus-visible:underline">
                {{ __('passwords.resend_code') }}
            </button>
        </form>
    </div>
</x-guest-layout>
