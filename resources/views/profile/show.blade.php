<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-2xl font-bold leading-tight text-ink">
            {{ __('profile.title') }}
        </h2>
    </x-slot>

    <div class="py-10 sm:py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <div class="overflow-hidden rounded-panel border border-forest/10 bg-white/90 p-6 shadow-soft sm:p-8">
                <h3 class="font-display text-lg font-bold text-ink">{{ __('profile.account_information') }}</h3>

                <div class="mt-5 flex items-center gap-4">
                    @if ($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ __('profile.avatar') }}" class="h-16 w-16 rounded-2xl object-cover ring-2 ring-forest/10">
                    @else
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-forest text-xl font-bold text-white">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif

                    <div>
                        <div class="font-semibold text-ink">{{ $user->name }}</div>
                        <div class="mt-0.5 text-sm text-stone">{{ $user->phone }}</div>
                        <div class="mt-2 flex flex-wrap gap-2 text-xs font-medium">
                            <span class="rounded-lg px-2.5 py-1 {{ $user->isPhoneVerified() ? 'bg-forest/10 text-forest' : 'bg-saffron/15 text-saffron' }}">
                                {{ __('profile.phone_verification') }}:
                                {{ $user->isPhoneVerified() ? __('profile.verified') : __('profile.not_verified') }}
                            </span>
                            <span class="rounded-lg bg-mist px-2.5 py-1 text-stone">
                                {{ __('profile.account_status') }}: {{ __('profile.status.'.$user->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-panel border border-forest/10 bg-white/90 shadow-soft">
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="p-6 sm:p-8">
                    @csrf
                    @method('PATCH')

                    <h3 class="font-display text-lg font-bold text-ink">{{ __('profile.update') }}</h3>

                    <div class="mt-5">
                        <x-input-label for="name" :value="__('profile.name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1.5 block w-full" :value="old('name', $user->name)" required autocomplete="name" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="avatar" :value="__('profile.avatar_help')" />
                        <input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1.5 block w-full text-sm text-stone file:me-3 file:rounded-lg file:border-0 file:bg-mist file:px-3 file:py-2 file:text-sm file:font-semibold file:text-ink hover:file:bg-forest/10">
                        <x-input-error :messages="$errors->get('avatar')" class="mt-2" />
                    </div>

                    <div class="mt-6">
                        <x-primary-button>{{ __('profile.save') }}</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden rounded-panel border border-forest/10 bg-white/90 p-6 shadow-soft sm:p-8">
                <h3 class="font-display text-lg font-bold text-ink">{{ __('profile.settings') }}</h3>
                <p class="mt-1 text-sm text-stone">{{ __('profile.language_help') }}</p>
                <div class="mt-4">
                    <x-input-label :value="__('navigation.language')" />
                    <div class="mt-2">
                        <x-locale-switcher />
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
