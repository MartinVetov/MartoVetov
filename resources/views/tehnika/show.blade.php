@php
    $faq = collect($category->faq ?? []);

    $schema = [[
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        'name' => $category->name.' под наем',
        'serviceType' => $category->name.' под наем',
        'areaServed' => ['@type' => 'Country', 'name' => 'България'],
        'provider' => ['@type' => 'Organization', 'name' => config('nt.brand'), 'url' => url('/')],
        'description' => $category->metaDescription(),
    ]];

    if ($faq->isNotEmpty()) {
        $schema[] = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $faq->map(fn ($item) => [
                '@type' => 'Question',
                'name' => $item['question'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['answer']],
            ])->all(),
        ];
    }
@endphp

<x-layouts.app
    :title="$category->seo_title ?: $category->name.' под наем'"
    :description="$category->metaDescription()"
    :schema="$schema"
    :breadcrumbs="[
        ['label' => 'Начало', 'url' => route('home')],
        ['label' => 'Техника', 'url' => route('categories.index')],
        ['label' => $category->name],
    ]"
>
    <section class="border-b border-ink-200 bg-ink-50">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid gap-10 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">{{ $category->name }} под наем</h1>
                    <p class="mt-4 text-lg text-ink-600">{{ $category->description }}</p>

                    @if ($category->children->isNotEmpty())
                        <ul class="mt-6 flex flex-wrap gap-2">
                            @foreach ($category->children as $child)
                                <li><x-ui.badge color="bg-white text-ink-700 ring-ink-200">{{ $child->name }}</x-ui.badge></li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <x-ui.card class="h-fit">
                    <h2 class="text-lg font-semibold">Търсиш {{ mb_strtolower($category->name) }}?</h2>
                    <p class="mt-2 text-sm text-ink-500">
                        Опиши какво трябва да свършиш. Ще насочим заявката към доставчици в твоя район.
                    </p>
                    <x-ui.button href="{{ route('leads.create', ['kategoriya' => $category->slug]) }}" size="lg" class="mt-5 w-full">
                        Намери {{ mb_strtolower($category->short_name ?: $category->name) }}
                    </x-ui.button>
                    <p class="mt-3 text-center text-xs text-ink-400">Безплатно и без ангажимент</p>
                </x-ui.card>
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-3">
            <div class="lg:col-span-2">
                @if ($category->seo_content)
                    <div class="prose-nt max-w-none">{!! $category->seo_content !!}</div>
                @endif

                @if ($faq->isNotEmpty())
                    <section class="mt-12">
                        <h2 class="text-2xl font-bold tracking-tight">Често задавани въпроси</h2>
                        <dl class="mt-6 divide-y divide-ink-200 border-t border-ink-200">
                            @foreach ($faq as $index => $item)
                                <div x-data="{ open: {{ $index === 0 ? 'true' : 'false' }} }" class="py-4">
                                    <dt>
                                        <button type="button" @click="open = !open" :aria-expanded="open"
                                                class="flex w-full items-center justify-between gap-4 text-left">
                                            <span class="text-base font-semibold text-ink-900">{{ $item['question'] }}</span>
                                            <svg class="h-5 w-5 shrink-0 text-ink-400 transition" :class="open && 'rotate-180'"
                                                 fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                            </svg>
                                        </button>
                                    </dt>
                                    <dd x-show="open" x-transition class="mt-3 text-ink-600">{{ $item['answer'] }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </section>
                @endif
            </div>

            <aside class="space-y-8">
                @if ($cities->isNotEmpty())
                    <section>
                        <h2 class="text-lg font-semibold">{{ $category->name }} по градове</h2>
                        <ul class="mt-3 space-y-1.5">
                            @foreach ($cities as $city)
                                <li>
                                    <a href="{{ route('categories.city', [$category, $city]) }}" class="text-sm text-ink-600 hover:text-brand-700">
                                        {{ $category->name }} {{ $city->name }} →
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            </aside>
        </div>

        @if ($providers->isNotEmpty())
            <section class="mt-14">
                <h2 class="text-2xl font-bold tracking-tight">Доставчици с {{ mb_strtolower($category->name) }}</h2>
                <p class="mt-2 text-ink-500">
                    Заявката ти стига до подходящите доставчици автоматично — не е нужно да им звъниш един по един.
                </p>
                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($providers as $provider)
                        @include('tehnika.partials.provider-card', ['provider' => $provider, 'category' => $category])
                    @endforeach
                </div>
            </section>
        @endif

        <section class="mt-14 rounded-2xl bg-ink-900 px-6 py-10 text-center sm:px-12">
            <h2 class="text-2xl font-bold text-white sm:text-3xl">Кажи какво трябва да свършиш</h2>
            <p class="mx-auto mt-3 max-w-2xl text-ink-300">
                Не е нужно да знаеш точния модел или размер. Опиши задачата и ние ще намерим техниката.
            </p>
            <x-ui.button href="{{ route('leads.create', ['kategoriya' => $category->slug]) }}" size="lg" class="mt-6">
                Намери техника
            </x-ui.button>
        </section>
    </div>
</x-layouts.app>
