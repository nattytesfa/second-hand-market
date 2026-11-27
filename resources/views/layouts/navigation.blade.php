<nav x-data="{ open: false }" class="relative z-50 border-b border-forest/10 bg-white/80 backdrop-blur-md">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="relative flex h-16 justify-between sm:h-[4.25rem]">
            <div class="flex items-center">
                <div class="flex shrink-0 items-center">
                    <a href="{{ route('home') }}">
                        <x-application-logo class="[&_span]:hidden sm:[&_span]:inline" />
                    </a>
                </div>

                <div class="hidden space-x-1 sm:-my-px sm:ms-8 sm:flex">
                    <x-nav-link :href="route('home')" :active="request()->routeIs('home')">
                        {{ __('navigation.home') }}
                    </x-nav-link>
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('navigation.dashboard') }}
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden items-center sm:ms-6 sm:flex">
                <x-dropdown align="right" width="64">
                    <x-slot name="trigger">
                        <button type="button" class="group inline-flex items-center gap-2 rounded-full py-1 pe-2 ps-1 text-sm font-medium text-ink transition hover:bg-mist focus:outline-none focus-visible:ring-2 focus-visible:ring-forest-soft/40">
                            @if (Auth::user()->avatar_url)
                                <img src="{{ Auth::user()->avatar_url }}" alt="" class="h-9 w-9 rounded-full object-cover ring-2 ring-forest/10">
                            @else
                                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-forest text-sm font-bold text-white ring-2 ring-forest/10">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </span>
                            @endif
                            <span class="hidden max-w-[7.5rem] truncate lg:inline">{{ Auth::user()->name }}</span>
                            <svg class="h-4 w-4 text-stone transition group-hover:text-ink" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="mb-1.5 rounded-xl bg-fog px-3 py-3">
                            <p class="truncate text-sm font-semibold text-ink">{{ Auth::user()->name }}</p>
                            <p class="mt-0.5 truncate text-xs text-stone">{{ Auth::user()->phone }}</p>
                        </div>

                        <x-dropdown-link :href="route('profile.edit')">
                            <svg class="h-4 w-4 shrink-0 text-stone" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                            <span>{{ __('navigation.profile') }}</span>
                        </x-dropdown-link>

                        @unless (Auth::user()->phone_verified_at)
                            <x-dropdown-link :href="route('otp.create')">
                                <svg class="h-4 w-4 shrink-0 text-stone" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 8.25h3m-3 3.75h3" />
                                </svg>
                                <span>{{ __('navigation.verify_phone') }}</span>
                            </x-dropdown-link>
                        @endunless

                        <div class="my-1.5 border-t border-forest/10"></div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    variant="danger"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                <svg class="h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                </svg>
                                <span>{{ __('navigation.log_out') }}</span>
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center rounded-xl p-2 text-stone transition hover:bg-mist hover:text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-forest-soft/40">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden border-t border-forest/10 sm:hidden">
        <div class="space-y-1 pb-3 pt-2">
            <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')">
                {{ __('navigation.home') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('navigation.dashboard') }}
            </x-responsive-nav-link>
        </div>

        <div class="border-t border-forest/10 pb-3 pt-4">
            <div class="flex items-center gap-3 px-4">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-forest text-sm font-bold text-white">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </span>
                <div>
                    <div class="text-base font-medium text-ink">{{ Auth::user()->name }}</div>
                    <div class="text-sm text-stone">{{ Auth::user()->phone }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')">
                    {{ __('navigation.profile') }}
                </x-responsive-nav-link>

                @unless (Auth::user()->phone_verified_at)
                    <x-responsive-nav-link :href="route('otp.create')">
                        {{ __('navigation.verify_phone') }}
                    </x-responsive-nav-link>
                @endunless

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('navigation.log_out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
