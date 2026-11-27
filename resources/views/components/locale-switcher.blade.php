@php
    $locales = config('app.supported_locales', ['en', 'am', 'om']);
    $current = app()->getLocale();
@endphp

<form method="POST" action="{{ route('locale.update') }}" class="flex flex-wrap items-center gap-1" aria-label="{{ __('navigation.language') }}">
    @csrf
    @foreach ($locales as $code)
        <button type="submit"
                name="locale"
                value="{{ $code }}"
                @if ($current === $code) aria-current="true" @endif
                class="rounded px-2 py-1 text-xs font-medium {{ $current === $code ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
            {{ __('locales.'.$code) }}
        </button>
    @endforeach
</form>
