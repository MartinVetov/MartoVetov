@props(['type' => 'info'])

@php
    $styles = [
        'success' => 'bg-emerald-50 text-emerald-900 border-emerald-200',
        'error' => 'bg-rose-50 text-rose-900 border-rose-200',
        'info' => 'bg-sky-50 text-sky-900 border-sky-200',
        'warning' => 'bg-amber-50 text-amber-900 border-amber-200',
    ];
@endphp

<div role="alert" {{ $attributes->merge(['class' => 'rounded-lg border px-4 py-3 text-sm '.($styles[$type] ?? $styles['info'])]) }}>
    {{ $slot }}
</div>
