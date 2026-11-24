<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-4 text-sm text-gray-600">
        {{ __('Enter the 6-digit code sent to your phone.') }}
        <span class="font-medium text-gray-900">{{ $phone }}</span>
    </div>

    <form method="POST" action="{{ route('otp.store') }}">
        @csrf

        <div>
            <x-input-label for="code" :value="__('Verification Code')" />
            <x-text-input id="code" class="block mt-1 w-full tracking-widest text-center" type="text" inputmode="numeric" name="code" :value="old('code')" required autofocus autocomplete="one-time-code" maxlength="{{ config('otp.length') }}" />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-4">
            <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" formnovalidate formaction="{{ route('otp.resend') }}">
                {{ __('Resend code') }}
            </button>

            <x-primary-button>
                {{ __('Verify') }}
            </x-primary-button>
        </div>
    </form>

    <div class="mt-4 text-sm">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="underline text-gray-600 hover:text-gray-900">
                {{ __('Log out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
