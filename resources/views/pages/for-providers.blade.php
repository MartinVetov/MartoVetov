<x-layouts.app
    title="Предлагаш техника?"
    description="Получавай заявки от клиенти, които търсят техника в твоя район. Регистрирай се безплатно като доставчик в „Намери Техник“."
    :breadcrumbs="[['label' => 'Начало', 'url' => route('home')], ['label' => 'Предлагам техника']]"
>
    <section class="border-b border-ink-200 bg-ink-900">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl">Предлагаш техника?</h1>
                <p class="mt-5 text-lg text-ink-200">
                    Получавай заявки от клиенти, които търсят техника в твоя район.
                    Без реклами на сляпо и без обаждания на студено.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <x-ui.button href="{{ route('register') }}" size="lg">Регистрирай се като доставчик</x-ui.button>
                    <x-ui.button href="{{ route('login') }}" size="lg" variant="outline"
                                 class="border-white/30 bg-white/10 text-white hover:bg-white/20">Вход в профила</x-ui.button>
                </div>
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold tracking-tight">Как става</h2>
        <ol class="mt-8 grid gap-5 md:grid-cols-4">
            @foreach ([
                ['Регистрация', 'Създай профил на фирмата за няколко минути.'],
                ['Добави техниката си', 'Опиши машините, които предлагаш, и дали са с оператор.'],
                ['Посочи районите', 'Избери градовете и радиуса, в който работиш.'],
                ['Получавай заявки', 'Изпращаме ти само заявки, които отговарят на техниката и района ти.'],
            ] as $i => [$stepTitle, $stepText])
                <li class="rounded-xl border border-ink-200 bg-white p-5 shadow-sm">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-600 text-sm font-bold text-white">{{ $i + 1 }}</span>
                    <h3 class="mt-3 font-semibold">{{ $stepTitle }}</h3>
                    <p class="mt-1.5 text-sm text-ink-500">{{ $stepText }}</p>
                </li>
            @endforeach
        </ol>

        <section class="mt-14">
            <h2 class="text-3xl font-bold tracking-tight">Категории, за които получаваме заявки</h2>
            <ul class="mt-6 flex flex-wrap gap-2">
                @foreach ($categories as $category)
                    <li><x-ui.badge color="bg-white text-ink-700 ring-ink-200">{{ $category->name }}</x-ui.badge></li>
                @endforeach
            </ul>
        </section>

        <section class="mt-14 grid gap-6 md:grid-cols-3">
            @foreach (config('nt.plans') as $key => $plan)
                <x-ui.card class="{{ $key === 'pro' ? 'ring-2 ring-brand-600' : '' }}">
                    <h3 class="text-lg font-semibold">{{ $plan['label'] }}</h3>
                    <ul class="mt-4 space-y-2 text-sm text-ink-600">
                        <li>• Заявки на месец: {{ $plan['monthly_lead_quota'] ?? 'без ограничение' }}</li>
                        <li>• Обяви за техника: {{ $plan['max_equipment'] ?? 'без ограничение' }}</li>
                        <li>• Снимки на машина: {{ $plan['max_images_per_equipment'] }}</li>
                        @if ($plan['priority'])<li>• Приоритетни заявки</li>@endif
                        @if ($plan['featured'])<li>• Открояване в резултатите</li>@endif
                    </ul>
                </x-ui.card>
            @endforeach
        </section>

        <p class="mt-6 text-sm text-ink-500">
            Плащаш за заявка (pay per lead), а не абонамент на сляпо. Цената зависи от категорията техника
            и се вижда преди да приемеш заявката.
        </p>

        <div class="mt-10">
            <x-ui.button href="{{ route('register') }}" size="lg">Регистрирай се като доставчик</x-ui.button>
        </div>
    </div>
</x-layouts.app>
