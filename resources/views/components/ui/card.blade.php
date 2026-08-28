@props(['padded' => true])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-ink-200 bg-white shadow-sm '.($padded ? 'p-5 sm:p-6' : '')]) }}>
    {{ $slot }}
</div>
