@props(['variant' => 'default'])

@php
$classes = match ($variant) {
    'danger' => 'flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-start text-sm font-medium text-red-700 transition hover:bg-red-50 focus:bg-red-50 focus:outline-none',
    default => 'flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-start text-sm font-medium text-ink transition hover:bg-mist focus:bg-mist focus:outline-none',
};
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
