<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-4 text-sm text-gray-600">
        {{ __('passwords.instructions') }}
    </div>

    <form method="POST" action="{{ route('password.request') }}">
        @csrf

        <div>
            <x-input-label for="phone" :value="__('login.phone_number')" />
            <x-text-input id="phone" class="block mt-1 w-full" type="tel" name="phone" :value="old('phone')" required autofocus autocomplete="tel" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('passwords.back_to_login') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('passwords.send_code') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
