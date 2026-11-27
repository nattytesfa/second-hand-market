<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center rounded-xl border border-forest/20 bg-white px-5 py-2.5 text-sm font-semibold text-ink shadow-sm transition hover:border-forest/35 hover:bg-fog focus:outline-none focus-visible:ring-2 focus-visible:ring-forest-soft/40 focus-visible:ring-offset-2 disabled:opacity-50']) }}>
    {{ $slot }}
</button>
