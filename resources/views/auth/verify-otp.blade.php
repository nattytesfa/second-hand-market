@php
    $maskedPhone = preg_replace('/(\d{2})\d{4}(\d{4})/', '$1****$2', $phone);
@endphp

<x-guest-layout>
    <div class="text-center">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-forest text-white shadow-soft">
            <svg class="h-7 w-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 8.25h3m-3 3.75h3" />
            </svg>
        </div>

        <h1 class="mt-5 font-display text-2xl font-bold text-ink">{{ __('phone.verify_title') }}</h1>
        <p class="mt-2 text-sm leading-relaxed text-stone">
            {{ __('phone.instructions') }}
        </p>
        <p class="mt-3 inline-flex items-center gap-2 rounded-full bg-mist px-3.5 py-1.5 text-sm font-semibold text-forest">
            <span class="h-1.5 w-1.5 rounded-full bg-forest-soft"></span>
            {{ $maskedPhone }}
        </p>
    </div>

    <x-auth-session-status class="mt-6" :status="session('status')" />

    <form method="POST" action="{{ route('otp.store') }}" class="mt-6 space-y-6">
        @csrf

        <div>
            <x-otp-input name="code" :value="old('code')" class="mx-auto" />
            <x-input-error :messages="$errors->get('code')" class="mt-3 text-center" />
        </div>

        <x-primary-button class="w-full py-3 text-base">
            {{ __('phone.verify') }}
        </x-primary-button>
    </form>

    <div class="mt-5 text-center">
        <p class="text-sm text-stone">{{ __('phone.didnt_get_code') }}</p>
        <form method="POST" action="{{ route('otp.resend') }}" class="mt-1.5">
            @csrf
            <button type="submit" class="text-sm font-semibold text-forest transition hover:text-forest-deep focus:outline-none focus-visible:underline">
                {{ __('phone.resend_code') }}
            </button>
        </form>
    </div>

    <div class="mt-8 border-t border-forest/10 pt-5 text-center">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm font-medium text-stone transition hover:text-ink focus:outline-none focus-visible:underline">
                {{ __('navigation.log_out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
