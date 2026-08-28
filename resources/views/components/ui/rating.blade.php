@props(['rating' => 0, 'count' => null])

<span class="inline-flex items-center gap-1.5" role="img" aria-label="Оценка {{ number_format((float) $rating, 1, ',', '') }} от 5">
    <span class="flex" aria-hidden="true">
        @for ($i = 1; $i <= 5; $i++)
            <svg class="h-4 w-4 {{ $i <= round($rating) ? 'text-amber-400' : 'text-ink-200' }}" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.05 2.93c.3-.92 1.6-.92 1.9 0l1.28 3.95a1 1 0 0 0 .95.69h4.15c.97 0 1.37 1.24.59 1.81l-3.36 2.44a1 1 0 0 0-.36 1.12l1.28 3.95c.3.92-.75 1.69-1.54 1.12l-3.36-2.44a1 1 0 0 0-1.17 0l-3.36 2.44c-.79.57-1.84-.2-1.54-1.12l1.28-3.95a1 1 0 0 0-.36-1.12L2.07 9.38c-.78-.57-.38-1.81.59-1.81h4.15a1 1 0 0 0 .95-.69l1.29-3.95Z" />
            </svg>
        @endfor
    </span>
    <span class="text-sm font-medium text-ink-700">{{ number_format((float) $rating, 1, ',', '') }}</span>
    @if ($count !== null)
        <span class="text-sm text-ink-500">({{ $count }})</span>
    @endif
</span>
