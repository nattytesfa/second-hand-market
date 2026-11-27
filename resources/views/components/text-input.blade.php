@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-xl border-forest/20 bg-white text-ink shadow-sm placeholder:text-stone-light focus:border-forest-soft focus:ring-forest-soft/40']) }}>
