@php
    $schema = [array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'LocalBusiness',
        'name' => $provider->company_name,
        'description' => $provider->description,
        'telephone' => $provider->phone,
        'url' => route('providers.show', $provider),
        'address' => array_filter([
            '@type' => 'PostalAddress',
            'addressLocality' => $provider->city?->name,
            'addressRegion' => $provider->city?->region,
            'addressCountry' => 'BG',
        ]),
        'aggregateRating' => $provider->rating_count > 0 ? [
            '@type' => 'AggregateRating',
            'ratingValue' => (string) $provider->rating_avg,
            'reviewCount' => (string) $provider->rating_count,
        ] : null,
    ])];

    $equipmentByCategory = $provider->equipment->groupBy(fn ($item) => $item->category?->name ?? 'Друга техника');
@endphp

<x-layouts.app
    :title="$provider->company_name"
    :description="Str::limit($provider->description ?: $provider->company_name.' — техника под наем в '.$provider->locationLabel().'.', 155)"
    :schema="$schema"
    :breadcrumbs="[
        ['label' => 'Начало', 'url' => route('home')],
        ['label' => 'Доставчици', 'url' => route('providers.index')],
        ['label' => $provider->company_name],
    ]"
>
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <div class="flex flex-wrap items-start gap-4">
                    @if ($provider->logoUrl())
                        <img src="{{ $provider->logoUrl() }}" alt="Лого на {{ $provider->company_name }}"
                             class="h-16 w-16 rounded-lg object-cover ring-1 ring-ink-200" loading="lazy">
                    @endif
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">{{ $provider->company_name }}</h1>
                        <p class="mt-2 text-ink-500">{{ $provider->locationLabel() }}</p>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    @if ($provider->verified)
                        <x-ui.badge color="bg-emerald-50 text-emerald-800 ring-emerald-200">✓ Проверен доставчик</x-ui.badge>
                    @endif
                    @if ($provider->company_verified)
                        <x-ui.badge>Потвърдена фирма</x-ui.badge>
                    @endif
                    @if ($provider->rating_count > 0)
                        <x-ui.rating :rating="$provider->rating_avg" :count="$provider->rating_count" />
                    @endif
                </div>

                @if ($provider->description)
                    <div class="prose-nt mt-6 max-w-none">
                        <p>{{ $provider->description }}</p>
                    </div>
                @endif

                <section class="mt-10">
                    <h2 class="text-2xl font-bold tracking-tight">Техника</h2>

                    @if ($provider->equipment->isEmpty())
                        <p class="mt-3 text-ink-500">Доставчикът още не е публикувал техника.</p>
                    @else
                        @foreach ($equipmentByCategory as $categoryName => $items)
                            <div class="mt-6">
                                <h3 class="text-sm font-semibold uppercase tracking-wide text-ink-500">{{ $categoryName }}</h3>
                                <ul class="mt-3 grid gap-3 sm:grid-cols-2">
                                    @foreach ($items as $item)
                                        <li class="rounded-lg border border-ink-200 bg-white p-4">
                                            <p class="font-medium text-ink-900">{{ $item->title() }}</p>
                                            <p class="mt-1 text-sm text-ink-500">
                                                {{ collect([
                                                    $item->year ? $item->year.' г.' : null,
                                                    $item->weight ? $item->weight.' т' : null,
                                                    $item->operator_available ? 'с оператор' : null,
                                                ])->filter()->implode(' · ') }}
                                            </p>
                                            @if ($item->priceLabel())
                                                <p class="mt-2 text-sm font-semibold text-brand-700">{{ $item->priceLabel() }}</p>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    @endif
                </section>

                <section class="mt-10">
                    <h2 class="text-2xl font-bold tracking-tight">Услуги</h2>
                    <ul class="mt-3 flex flex-wrap gap-2">
                        @if ($provider->hasOperator())
                            <li><x-ui.badge>С оператор</x-ui.badge></li>
                        @endif
                        @if ($provider->hasEquipmentWithoutOperator())
                            <li><x-ui.badge>Без оператор</x-ui.badge></li>
                        @endif
                        <li><x-ui.badge>Радиус на обслужване: {{ $provider->service_radius }} км</x-ui.badge></li>
                    </ul>
                </section>

                @if ($provider->reviews->isNotEmpty())
                    <section class="mt-10">
                        <h2 class="text-2xl font-bold tracking-tight">Отзиви</h2>
                        <ul class="mt-4 space-y-4">
                            @foreach ($provider->reviews as $review)
                                <li class="rounded-lg border border-ink-200 bg-white p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="font-medium text-ink-900">{{ $review->author_name }}</p>
                                        <x-ui.rating :rating="$review->rating" />
                                    </div>
                                    @if ($review->comment)
                                        <p class="mt-2 text-sm text-ink-600">{{ $review->comment }}</p>
                                    @endif
                                    <p class="mt-2 text-xs text-ink-400">{{ $review->created_at->format('d.m.Y') }}</p>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            </div>

            <aside class="space-y-6">
                <x-ui.card class="h-fit lg:sticky lg:top-24">
                    <h2 class="text-lg font-semibold">Изпрати запитване</h2>
                    <p class="mt-2 text-sm text-ink-500">
                        Опиши задачата си. Ще насочим заявката към {{ $provider->company_name }} и други подходящи доставчици.
                    </p>
                    <x-ui.button href="{{ route('leads.create') }}" size="lg" class="mt-5 w-full">Изпрати запитване</x-ui.button>

                    <dl class="mt-6 space-y-3 border-t border-ink-100 pt-5 text-sm">
                        <div>
                            <dt class="text-ink-500">Обслужва</dt>
                            <dd class="mt-0.5 font-medium text-ink-900">
                                {{ $provider->serviceAreas->pluck('city.name')->filter()->take(6)->implode(', ') ?: $provider->locationLabel() }}
                            </dd>
                        </div>
                        @if ($provider->working_hours)
                            <div>
                                <dt class="text-ink-500">Работно време</dt>
                                <dd class="mt-0.5 font-medium text-ink-900">{{ $provider->working_hours }}</dd>
                            </div>
                        @endif
                        @if ($provider->website)
                            <div>
                                <dt class="text-ink-500">Уебсайт</dt>
                                <dd class="mt-0.5"><a href="{{ $provider->website }}" rel="nofollow noopener" target="_blank" class="font-medium text-brand-700 hover:underline">{{ $provider->website }}</a></dd>
                            </div>
                        @endif
                    </dl>
                </x-ui.card>
            </aside>
        </div>
    </div>
</x-layouts.app>
