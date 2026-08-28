@props(['provider', 'category' => null])

<article class="flex flex-col rounded-xl border border-ink-200 bg-white p-5 shadow-sm">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <h3 class="truncate text-base font-semibold">
                <a href="{{ route('providers.show', $provider) }}" class="hover:text-brand-700">{{ $provider->company_name }}</a>
            </h3>
            <p class="mt-0.5 text-sm text-ink-500">{{ $provider->locationLabel() }}</p>
        </div>
        @if ($provider->verified)
            <x-ui.badge color="bg-emerald-50 text-emerald-800 ring-emerald-200">Проверен</x-ui.badge>
        @endif
    </div>

    @if ($provider->rating_count > 0)
        <div class="mt-3"><x-ui.rating :rating="$provider->rating_avg" :count="$provider->rating_count" /></div>
    @endif

    @if ($provider->equipment->isNotEmpty())
        <ul class="mt-3 space-y-1 text-sm text-ink-600">
            @foreach ($provider->equipment->take(3) as $item)
                <li>• {{ $item->title() }}</li>
            @endforeach
        </ul>
    @endif

    <a href="{{ route('providers.show', $provider) }}" class="mt-4 text-sm font-semibold text-brand-700 hover:text-brand-800">
        Виж профила →
    </a>
</article>
