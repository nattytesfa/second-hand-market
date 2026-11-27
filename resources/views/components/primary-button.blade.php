<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-xl border border-transparent bg-forest px-5 py-2.5 text-sm font-semibold text-white shadow-soft transition hover:bg-forest-deep focus:outline-none focus-visible:ring-2 focus-visible:ring-forest-soft/50 focus-visible:ring-offset-2 disabled:opacity-50']) }}>
    {{ $slot }}
</button>
