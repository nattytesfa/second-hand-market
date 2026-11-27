<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-xl border border-transparent bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-soft transition hover:bg-red-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500/50 focus-visible:ring-offset-2 active:bg-red-700']) }}>
    {{ $slot }}
</button>
