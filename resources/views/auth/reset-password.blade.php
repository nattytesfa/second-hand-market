<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-4 text-sm text-gray-600">
        {{ __('passwords.reset_instructions') }}
        <span class="font-medium text-gray-900">{{ $phone }}</span>
    </div>

    <form method="POST" action="{{ route('password.reset') }}">
        @csrf

        <div>
            <x-input-label for="code" :value="__('passwords.code_label')" />
            <x-text-input id="code" class="block mt-1 w-full tracking-widest text-center" type="text" inputmode="numeric" name="code" :value="old('code')" required autofocus autocomplete="one-time-code" maxlength="{{ config('otp.length') }}" />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('passwords.new_password')" />
            <x-password-input id="password" class="block mt-1 w-full" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('passwords.confirm_new_password')" />
            <x-password-input id="password_confirmation" class="block mt-1 w-full" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-4">
            <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" formnovalidate formaction="{{ route('password.resend') }}">
                {{ __('passwords.resend_code') }}
            </button>

            <x-primary-button>
                {{ __('passwords.submit') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
