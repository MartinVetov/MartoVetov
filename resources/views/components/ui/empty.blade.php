@props(['title' => 'Няма записи', 'description' => null])

<div class="rounded-xl border border-dashed border-ink-300 bg-ink-50 px-6 py-12 text-center">
    <p class="text-base font-medium text-ink-800">{{ $title }}</p>
    @if ($description)
        <p class="mt-1.5 text-sm text-ink-500">{{ $description }}</p>
    @endif
    @if (trim($slot))
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>
