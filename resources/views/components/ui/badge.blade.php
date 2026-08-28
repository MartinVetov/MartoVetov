@props(['color' => 'bg-ink-100 text-ink-700 ring-ink-200'])

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset '.$color]) }}>
    {{ $slot }}
</span>
