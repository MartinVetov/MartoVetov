<x-layouts.app
    :description="'Опиши какво трябва да свършиш, къде и кога. Ще намерим подходящи доставчици на мини багери, автовишки, самосвали и друга специализирана техника в твоя район.'"
    :schema="[[
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => config('nt.brand'),
        'url' => url('/'),
        'inLanguage' => 'bg-BG',
    ]]"
>
    {{-- Hero --}}
    <section class="relative overflow-hidden border-b border-ink-200 bg-ink-900">
        <div class="absolute inset-0 opacity-20" aria-hidden="true"
             style="background-image: radial-gradient(circle at 20% 20%, #f97316 0, transparent 45%), radial-gradient(circle at 80% 0%, #38bdf8 0, transparent 40%);"></div>

        <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
            <div class="max-w-3xl">
                <p class="mb-4 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-sm font-medium text-brand-200">
                    Не търси техника. Кажи какво трябва да свършиш.
                </p>

                <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
                    Намери техниката за твоята задача
                </h1>

                <p class="mt-5 text-lg text-ink-200 sm:text-xl">
                    Опиши какво трябва да свършиш, къде и кога. Ще намерим подходящи доставчици на техника.
                </p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <x-ui.button href="{{ route('leads.create') }}" size="lg" class="w-full sm:w-auto">
                        Намери техника
                    </x-ui.button>
                    <x-ui.button href="{{ route('for-providers') }}" size="lg" variant="outline"
                                 class="w-full border-white/30 bg-white/10 text-white hover:bg-white/20 sm:w-auto">
                        Предлагам техника
                    </x-ui.button>
                </div>

                <dl class="mt-10 grid max-w-lg grid-cols-3 gap-6 text-white">
                    <div>
                        <dt class="text-sm text-ink-300">Доставчици</dt>
                        <dd class="text-2xl font-semibold">{{ $stats['providers'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-ink-300">Населени места</dt>
                        <dd class="text-2xl font-semibold">{{ $stats['cities'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-ink-300">Категории техника</dt>
                        <dd class="text-2xl font-semibold">{{ $stats['categories'] }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>

    {{-- Популярни категории --}}
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold tracking-tight">Популярни категории</h2>
                <p class="mt-2 text-ink-500">Избери категория или директно опиши задачата си.</p>
            </div>
            <a href="{{ route('categories.index') }}" class="text-sm font-semibold text-brand-700 hover:text-brand-800">
                Виж всички категории →
            </a>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($categories as $category)
                <a href="{{ route('categories.show', $category) }}"
                   class="group flex flex-col justify-between rounded-xl border border-ink-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-300 hover:shadow-md">
                    <div>
                        <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-brand-50 text-xl" aria-hidden="true">
                            {{ $category->icon ?: '🚜' }}
                        </span>
                        <h3 class="mt-4 text-lg font-semibold group-hover:text-brand-700">{{ $category->name }}</h3>
                        <p class="mt-1.5 line-clamp-2 text-sm text-ink-500">{{ $category->description }}</p>
                    </div>
                    <p class="mt-4 text-sm font-medium text-ink-400">
                        {{ $category->equipment_count }} {{ $category->equipment_count === 1 ? 'машина' : 'машини' }} в платформата
                    </p>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Как работи --}}
    <section class="border-y border-ink-200 bg-ink-50">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold tracking-tight">Как работи</h2>
            <p class="mt-2 max-w-2xl text-ink-500">
                Не се налага да обикаляш десетки сайтове и да звъниш на различни фирми.
            </p>

            <ol class="mt-10 grid gap-6 md:grid-cols-3">
                @foreach ([
                    ['1', 'Опиши задачата', 'Кажи какво трябва да свършиш. Ако не знаеш каква техника ти трябва — просто го отбележи.'],
                    ['2', 'Намираме подходяща техника', 'Свързваме заявката ти с доставчици в твоя район, които разполагат с нужната машина.'],
                    ['3', 'Получаваш оферти', 'Подходящите доставчици се свързват с теб, за да уточните цена и срок.'],
                ] as [$step, $stepTitle, $stepText])
                    <li class="rounded-xl border border-ink-200 bg-white p-6 shadow-sm">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-600 text-lg font-bold text-white">{{ $step }}</span>
                        <h3 class="mt-4 text-lg font-semibold">{{ $stepTitle }}</h3>
                        <p class="mt-2 text-sm text-ink-500">{{ $stepText }}</p>
                    </li>
                @endforeach
            </ol>

            <div class="mt-10">
                <x-ui.button href="{{ route('leads.create') }}" size="lg">Опиши задачата си</x-ui.button>
            </div>
        </div>
    </section>

    {{-- Не знаеш каква техника --}}
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid items-center gap-10 rounded-2xl border border-ink-200 bg-white p-8 shadow-sm lg:grid-cols-2 lg:p-12">
            <div>
                <h2 class="text-3xl font-bold tracking-tight">Не знаеш каква техника ти трябва?</h2>
                <p class="mt-4 text-ink-600">
                    Няма проблем. Не е нужно да знаеш дали ти трябва мини багер до 2 тона или до 5 тона.
                    Опиши задачата с прости думи — например „трябва да изкопая канал около 30 метра в двора“ —
                    и ние ще насочим заявката към доставчици, които могат да преценят.
                </p>
                <div class="mt-6">
                    <x-ui.button href="{{ route('leads.create') }}" size="lg">Опиши задачата си</x-ui.button>
                </div>
            </div>

            <figure class="rounded-xl border border-ink-200 bg-ink-50 p-6">
                <blockquote class="text-lg text-ink-800">
                    „Трябва ми изкоп за основи на къща, около 30 метра. Дворът е с тесен вход.“
                </blockquote>
                <figcaption class="mt-4 text-sm text-ink-500">
                    Достатъчно е толкова. Останалото уточняваме с няколко въпроса.
                </figcaption>
            </figure>
        </div>
    </section>

    {{-- Градове --}}
    @if ($cities->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold tracking-tight">Техника под наем по градове</h2>
            <div class="mt-6 flex flex-wrap gap-2">
                @foreach ($cities as $city)
                    @php $firstCategory = $categories->first(); @endphp
                    @if ($firstCategory)
                        <a href="{{ route('categories.city', [$firstCategory, $city]) }}"
                           class="rounded-full border border-ink-200 bg-white px-4 py-2 text-sm font-medium text-ink-700 transition hover:border-brand-300 hover:text-brand-700">
                            {{ $firstCategory->name }} {{ $city->name }}
                        </a>
                    @endif
                @endforeach
            </div>
        </section>
    @endif

    {{-- CTA за доставчици --}}
    <section class="border-t border-ink-200 bg-ink-900">
        <div class="mx-auto flex max-w-7xl flex-col items-start justify-between gap-6 px-4 py-14 sm:px-6 lg:flex-row lg:items-center lg:px-8">
            <div>
                <h2 class="text-2xl font-bold text-white sm:text-3xl">Предлагаш техника?</h2>
                <p class="mt-2 max-w-2xl text-ink-300">
                    Получавай заявки от клиенти, които търсят техника в твоя район. Без обаждания на сляпо.
                </p>
            </div>
            <x-ui.button href="{{ route('register') }}" size="lg">Регистрирай се като доставчик</x-ui.button>
        </div>
    </section>
</x-layouts.app>
