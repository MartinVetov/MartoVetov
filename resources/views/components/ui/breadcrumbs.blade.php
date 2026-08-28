@props(['items' => []])

@php
    $trail = collect($items);
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $trail->values()->map(fn ($item, $i) => array_filter([
            '@type' => 'ListItem',
            'position' => $i + 1,
            'name' => $item['label'],
            'item' => $item['url'] ?? null,
        ]))->all(),
    ];
@endphp

<nav aria-label="Навигация по нива" class="border-b border-ink-100 bg-ink-50">
    <div class="mx-auto max-w-7xl px-4 py-3 sm:px-6 lg:px-8">
        <ol class="flex flex-wrap items-center gap-1.5 text-sm text-ink-500">
            @foreach ($trail as $item)
                <li class="flex items-center gap-1.5">
                    @if (! $loop->first)
                        <span aria-hidden="true" class="text-ink-300">/</span>
                    @endif
                    @if (! empty($item['url']) && ! $loop->last)
                        <a href="{{ $item['url'] }}" class="hover:text-brand-700">{{ $item['label'] }}</a>
                    @else
                        <span class="font-medium text-ink-700" @if ($loop->last) aria-current="page" @endif>{{ $item['label'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </div>
</nav>

<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
