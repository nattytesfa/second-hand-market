@props(['variant' => 'default'])

@php
    $locales = config('app.supported_locales', ['en', 'am', 'om']);
    $current = app()->getLocale();
    $isHero = $variant === 'hero';
@endphp

<form method="POST" action="{{ route('locale.update') }}" class="flex flex-wrap items-center gap-1" aria-label="{{ __('navigation.language') }}">
    @csrf
    @foreach ($locales as $code)
        <button type="submit"
                name="locale"
                value="{{ $code }}"
                @if ($current === $code) aria-current="true" @endif
                class="rounded-lg px-2.5 py-1 text-xs font-semibold tracking-wide transition
                    {{ $current === $code
                        ? ($isHero ? 'bg-white text-forest' : 'bg-forest text-white')
                        : ($isHero ? 'text-white/80 hover:bg-white/15 hover:text-white' : 'text-stone hover:bg-mist hover:text-ink') }}">
            {{ __('locales.'.$code) }}
        </button>
    @endforeach
</form>
