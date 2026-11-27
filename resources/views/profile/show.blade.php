<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('profile.title') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium">{{ __('profile.account_information') }}</h3>

                    <div class="mt-4 flex items-center gap-4">
                        @if ($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" alt="{{ __('profile.avatar') }}" class="h-16 w-16 rounded-full object-cover">
                        @else
                            <div class="h-16 w-16 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif

                        <div>
                            <div class="font-medium">{{ $user->name }}</div>
                            <div class="text-sm text-gray-600">{{ $user->phone }}</div>
                            <div class="text-sm text-gray-600">
                                {{ __('profile.phone_verification') }}:
                                {{ $user->isPhoneVerified() ? __('profile.verified') : __('profile.not_verified') }}
                            </div>
                            <div class="text-sm text-gray-600">
                                {{ __('profile.account_status') }}: {{ __('profile.status.'.$user->status) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="p-6">
                    @csrf
                    @method('PATCH')

                    <h3 class="text-lg font-medium">{{ __('profile.update') }}</h3>

                    <div class="mt-4">
                        <x-input-label for="name" :value="__('profile.name')" />
                        <x-text-input id="name" name="name" type="text" class="block mt-1 w-full" :value="old('name', $user->name)" required autocomplete="name" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="avatar" :value="__('profile.avatar_help')" />
                        <input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm text-gray-600">
                        <x-input-error :messages="$errors->get('avatar')" class="mt-2" />
                    </div>

                    <div class="mt-6">
                        <x-primary-button>{{ __('profile.save') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
