@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-xl border border-forest/15 bg-forest/5 px-3.5 py-2.5 text-sm font-medium text-forest']) }}>
        {{ $status }}
    </div>
@endif
