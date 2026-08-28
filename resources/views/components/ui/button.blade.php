@props([
    'href' => null,
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'submit',
])

@php
    $base = 'btn inline-flex items-center justify-center gap-2 rounded-lg font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 disabled:opacity-60 disabled:pointer-events-none';

    $variants = [
        'primary' => 'bg-brand-600 text-white hover:bg-brand-700 focus-visible:outline-brand-600',
        'secondary' => 'bg-ink-900 text-white hover:bg-ink-800 focus-visible:outline-ink-900',
        'outline' => 'border border-ink-300 bg-white text-ink-800 hover:bg-ink-50',
        'ghost' => 'text-ink-600 hover:bg-ink-100 hover:text-ink-900',
        'danger' => 'bg-rose-600 text-white hover:bg-rose-700',
        'success' => 'bg-emerald-600 text-white hover:bg-emerald-700',
    ];

    $sizes = [
        'sm' => 'px-3.5 py-2 text-sm',
        'md' => 'px-5 py-2.5 text-sm',
        'lg' => 'px-6 py-3.5 text-base',
    ];

    $classes = $base.' '.($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? $sizes['md']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
