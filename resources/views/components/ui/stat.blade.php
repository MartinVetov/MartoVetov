@props(['label', 'value', 'hint' => null, 'accent' => false])

<div class="rounded-xl border border-ink-200 bg-white p-4 shadow-sm sm:p-5">
    <p class="text-sm font-medium text-ink-500">{{ $label }}</p>
    <p class="mt-1.5 text-2xl font-semibold tracking-tight {{ $accent ? 'text-brand-600' : 'text-ink-900' }} sm:text-3xl">{{ $value }}</p>
    @if ($hint)
        <p class="mt-1 text-xs text-ink-500">{{ $hint }}</p>
    @endif
</div>
