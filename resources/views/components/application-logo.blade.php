@props([
    'markOnly' => false,
    'variant' => 'default', // default | light
])

@php
    $isLight = $variant === 'light';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    <svg class="h-8 w-8 shrink-0" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <rect width="40" height="40" rx="10" fill="{{ $isLight ? '#F3F7F5' : '#0F3D34' }}"/>
        <path d="M11 26.5V13.5L20 8.5L29 13.5V26.5L20 31.5L11 26.5Z" stroke="{{ $isLight ? '#0F3D34' : '#F3F7F5' }}" stroke-width="1.6" stroke-linejoin="round"/>
        <path d="M20 8.5V31.5M11 13.5L29 26.5M29 13.5L11 26.5" stroke="#E0A912" stroke-width="1.4" stroke-linecap="round"/>
    </svg>
    @unless ($markOnly)
        <span @class([
            'font-display text-lg font-bold tracking-tight leading-none',
            'text-white' => $isLight,
            'text-ink' => ! $isLight,
        ])>
            {{ config('app.name', 'Adama Second-Hand Market') }}
        </span>
    @endunless
</span>
