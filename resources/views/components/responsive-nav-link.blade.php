@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full border-l-4 border-saffron bg-mist ps-3 pe-4 py-2 text-start text-base font-semibold text-forest transition duration-150 ease-in-out focus:outline-none focus:bg-mist focus:text-forest-deep'
            : 'block w-full border-l-4 border-transparent ps-3 pe-4 py-2 text-start text-base font-medium text-stone transition duration-150 ease-in-out hover:border-forest/20 hover:bg-fog hover:text-ink focus:outline-none focus:border-forest/20 focus:bg-fog focus:text-ink';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
