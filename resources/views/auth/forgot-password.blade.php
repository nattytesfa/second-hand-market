<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-4 text-sm text-gray-600">
        {{ __('Forgot your password? Enter your phone number and we will send you a verification code.') }}
    </div>

    <form method="POST" action="{{ route('password.request') }}">
        @csrf

        <div>
            <x-input-label for="phone" :value="__('Phone Number')" />
            <x-text-input id="phone" class="block mt-1 w-full" type="tel" name="phone" :value="old('phone')" required autofocus autocomplete="tel" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Back to login') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Send Code') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
