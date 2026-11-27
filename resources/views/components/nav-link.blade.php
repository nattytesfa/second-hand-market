@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center border-b-2 border-saffron px-1 pt-1 text-sm font-semibold leading-5 text-ink transition duration-150 ease-in-out focus:outline-none focus:border-saffron-bright'
            : 'inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium leading-5 text-stone transition duration-150 ease-in-out hover:border-forest/25 hover:text-ink focus:outline-none focus:border-forest/25 focus:text-ink';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
