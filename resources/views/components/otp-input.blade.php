@props([
    'name' => 'code',
    'id' => 'code',
    'length' => null,
    'value' => '',
])

@php
    $length = max(4, (int) ($length ?? config('otp.length', 6)));
    $initial = substr(preg_replace('/\D/', '', (string) $value) ?? '', 0, $length);
@endphp

<div
    x-data="{
        length: {{ $length }},
        digits: @js(array_values(array_pad(str_split($initial !== '' ? $initial : ''), $length, ''))),
        get code() {
            return this.digits.join('');
        },
        focusAt(index) {
            this.$refs['d' + index]?.focus();
            this.$refs['d' + index]?.select();
        },
        setDigit(index, raw) {
            const digit = String(raw || '').replace(/\D/g, '').slice(-1);
            this.digits[index] = digit;
            if (digit && index < this.length - 1) {
                this.focusAt(index + 1);
            }
        },
        onKeydown(index, event) {
            if (event.key === 'Backspace') {
                event.preventDefault();
                if (this.digits[index]) {
                    this.digits[index] = '';
                    return;
                }
                if (index > 0) {
                    this.digits[index - 1] = '';
                    this.focusAt(index - 1);
                }
                return;
            }
            if (event.key === 'ArrowLeft' && index > 0) {
                event.preventDefault();
                this.focusAt(index - 1);
            }
            if (event.key === 'ArrowRight' && index < this.length - 1) {
                event.preventDefault();
                this.focusAt(index + 1);
            }
        },
        onPaste(event) {
            const text = (event.clipboardData || window.clipboardData).getData('text') || '';
            const pasted = text.replace(/\D/g, '').slice(0, this.length);
            if (! pasted) return;
            event.preventDefault();
            for (let i = 0; i < this.length; i++) {
                this.digits[i] = pasted[i] || '';
            }
            this.focusAt(Math.min(pasted.length, this.length - 1));
        }
    }"
    x-init="$nextTick(() => { const i = digits.findIndex(d => !d); focusAt(i === -1 ? length - 1 : i) })"
    {{ $attributes->class('w-full') }}
>
    <input type="hidden" name="{{ $name }}" id="{{ $id }}" :value="code" autocomplete="one-time-code">

    <div class="flex justify-center gap-2 sm:gap-2.5" role="group" aria-label="{{ __('phone.code_label') }}" @paste="onPaste($event)">
        @for ($i = 0; $i < $length; $i++)
            <input
                type="text"
                inputmode="numeric"
                pattern="[0-9]*"
                maxlength="1"
                autocomplete="off"
                x-ref="d{{ $i }}"
                x-model="digits[{{ $i }}]"
                @input="setDigit({{ $i }}, $event.target.value)"
                @keydown="onKeydown({{ $i }}, $event)"
                @focus="$event.target.select()"
                aria-label="{{ __('phone.code_label') }} {{ $i + 1 }}"
                class="h-14 w-11 rounded-xl border border-forest/20 bg-fog text-center font-display text-2xl font-bold text-ink shadow-sm transition focus:border-forest-soft focus:bg-white focus:outline-none focus:ring-2 focus:ring-forest-soft/30 sm:h-16 sm:w-12 sm:text-[1.75rem]"
            >
        @endfor
    </div>
</div>
