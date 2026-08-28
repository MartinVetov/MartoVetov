@php
    $title = "{$category->name} под наем в {$city->name}";
    $description = $city->seo_description
        ?: "Търсиш {$category->name} под наем в {$city->name}? Опиши задачата си и ще насочим заявката към доставчици, които обслужват {$city->name} и областта.";

    $schema = [[
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        'name' => $title,
        'serviceType' => $category->name.' под наем',
        'areaServed' => ['@type' => 'City', 'name' => $city->name],
        'provider' => ['@type' => 'Organization', 'name' => config('nt.brand'), 'url' => url('/')],
        'description' => $description,
    ]];
@endphp

<x-layouts.app
    :title="$city->seo_title ?: $title"
    :description="$description"
    :schema="$schema"
    :breadcrumbs="[
        ['label' => 'Начало', 'url' => route('home')],
        ['label' => 'Техника', 'url' => route('categories.index')],
        ['label' => $category->name, 'url' => route('categories.show', $category)],
        ['label' => $city->name],
    ]"
>
    <section class="border-b border-ink-200 bg-ink-50">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid gap-10 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">{{ $title }}</h1>
                    <p class="mt-4 text-lg text-ink-600">
                        Опиши задачата си и ще насочим заявката към доставчици, които обслужват
                        {{ $city->name }} и област {{ $city->region }}.
                    </p>
                    <dl class="mt-6 flex flex-wrap gap-x-8 gap-y-3 text-sm">
                        <div>
                            <dt class="text-ink-500">Доставчици в района</dt>
                            <dd class="text-lg font-semibold text-ink-900">{{ $providers->count() }}</dd>
                        </div>
                        <div>
                            <dt class="text-ink-500">Област</dt>
                            <dd class="text-lg font-semibold text-ink-900">{{ $city->region }}</dd>
                        </div>
                    </dl>
                </div>

                <x-ui.card class="h-fit">
                    <h2 class="text-lg font-semibold">Заяви {{ mb_strtolower($category->name) }} в {{ $city->name }}</h2>
                    <p class="mt-2 text-sm text-ink-500">Безплатно. Доставчиците се свързват с теб.</p>
                    <x-ui.button href="{{ route('leads.create', ['kategoriya' => $category->slug, 'grad' => $city->slug]) }}"
                                 size="lg" class="mt-5 w-full">
                        Намери техника в {{ $city->name }}
                    </x-ui.button>
                </x-ui.card>
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-3">
            <div class="lg:col-span-2">
                @if ($city->seo_content)
                    <div class="prose-nt max-w-none">{!! $city->seo_content !!}</div>
                @else
                    <div class="prose-nt max-w-none">
                        <h2>{{ $category->name }} под наем в {{ $city->name }}</h2>
                        <p>
                            {{ $city->name }} е сред градовете с най-активно строителство в област {{ $city->region }}.
                            Заявките за {{ mb_strtolower($category->name) }} тук са ежедневие — от изкопи за основи и
                            подземни комуникации до разчистване на дворни места.
                        </p>
                        <p>
                            Вместо да обикаляш сайтове и да звъниш на десетки фирми, опиши какво трябва да свършиш.
                            Заявката отива към доставчиците, които реално обслужват {{ $city->name }}
                            и разполагат със свободна техника за твоята дата.
                        </p>

                        <h3>Какво влияе на цената</h3>
                        <ul>
                            <li>размерът и типът на машината;</li>
                            <li>дали е нужен оператор;</li>
                            <li>продължителността на работата;</li>
                            <li>отдалечеността на обекта и транспортът до него;</li>
                            <li>достъпът до обекта и типът терен.</li>
                        </ul>

                        <h3>Как да подготвиш обекта</h3>
                        <p>
                            Осигури свободен достъп до мястото, премахни паркирани автомобили и провери ширината на входа.
                            Ако има подземни комуникации — ток, вода, газ — уведоми доставчика предварително.
                        </p>
                    </div>
                @endif
            </div>

            <aside class="space-y-8">
                <section>
                    <h2 class="text-lg font-semibold">Същата техника в други градове</h2>
                    <ul class="mt-3 space-y-1.5">
                        @foreach ($cities as $otherCity)
                            <li>
                                <a href="{{ route('categories.city', [$category, $otherCity]) }}" class="text-sm text-ink-600 hover:text-brand-700">
                                    {{ $category->name }} {{ $otherCity->name }} →
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>

                <section>
                    <h2 class="text-lg font-semibold">Друга техника в {{ $city->name }}</h2>
                    <ul class="mt-3 space-y-1.5">
                        @foreach ($otherCategories as $otherCategory)
                            <li>
                                <a href="{{ route('categories.city', [$otherCategory, $city]) }}" class="text-sm text-ink-600 hover:text-brand-700">
                                    {{ $otherCategory->name }} {{ $city->name }} →
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            </aside>
        </div>

        @if ($providers->isNotEmpty())
            <section class="mt-14">
                <h2 class="text-2xl font-bold tracking-tight">Доставчици в {{ $city->name }}</h2>
                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($providers as $provider)
                        @include('tehnika.partials.provider-card', ['provider' => $provider, 'category' => $category])
                    @endforeach
                </div>
            </section>
        @else
            <section class="mt-14">
                <x-ui.empty
                    title="Още няма регистрирани доставчици за {{ $city->name }}"
                    description="Изпрати заявка — свързваме се и с доставчици от съседни градове, които обслужват района.">
                    <x-ui.button href="{{ route('leads.create', ['kategoriya' => $category->slug, 'grad' => $city->slug]) }}">
                        Изпрати заявка
                    </x-ui.button>
                </x-ui.empty>
            </section>
        @endif
    </div>
</x-layouts.app>
