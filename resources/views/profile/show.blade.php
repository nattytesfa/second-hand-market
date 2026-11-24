<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium">{{ __('Account information') }}</h3>

                    <div class="mt-4 flex items-center gap-4">
                        @if ($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" alt="{{ __('Avatar') }}" class="h-16 w-16 rounded-full object-cover">
                        @else
                            <div class="h-16 w-16 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif

                        <div>
                            <div class="font-medium">{{ $user->name }}</div>
                            <div class="text-sm text-gray-600">{{ $user->phone }}</div>
                            <div class="text-sm text-gray-600">
                                {{ __('Phone verification') }}:
                                {{ $user->isPhoneVerified() ? __('Verified') : __('Not verified') }}
                            </div>
                            <div class="text-sm text-gray-600">
                                {{ __('Account status') }}: {{ ucfirst($user->status) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="p-6">
                    @csrf
                    @method('PATCH')

                    <h3 class="text-lg font-medium">{{ __('Update profile') }}</h3>

                    <div class="mt-4">
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" name="name" type="text" class="block mt-1 w-full" :value="old('name', $user->name)" required autocomplete="name" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="avatar" :value="__('Avatar (JPEG, PNG or WebP, max 2 MB)')" />
                        <input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm text-gray-600">
                        <x-input-error :messages="$errors->get('avatar')" class="mt-2" />
                    </div>

                    <div class="mt-6">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
