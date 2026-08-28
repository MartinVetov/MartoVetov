<x-layouts.app
    title="Доставчици на техника"
    description="Регистрирани доставчици на строителна и специализирана техника в България. Филтрирай по град и вид техника."
    :breadcrumbs="[
        ['label' => 'Начало', 'url' => route('home')],
        ['label' => 'Доставчици'],
    ]"
>
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <header class="max-w-3xl">
            <h1 class="text-4xl font-bold tracking-tight">Доставчици на техника</h1>
            <p class="mt-4 text-lg text-ink-600">
                Разгледай кой предлага техника в твоя район. Но по-бързият път е да опишеш задачата си —
                ние ще насочим заявката към подходящите доставчици.
            </p>
            <div class="mt-6">
                <x-ui.button href="{{ route('leads.create') }}" size="lg">Намери техника</x-ui.button>
            </div>
        </header>

        <form method="GET" class="mt-10 grid gap-3 rounded-xl border border-ink-200 bg-ink-50 p-4 sm:grid-cols-3">
            <x-ui.select name="grad" label="Град" placeholder="Всички градове"
                         :options="$cities->pluck('name', 'slug')" :value="request('grad')" />
            <x-ui.select name="tehnika" label="Вид техника" placeholder="Всякаква техника"
                         :options="$categories->pluck('name', 'slug')" :value="request('tehnika')" />
            <div class="flex items-end">
                <x-ui.button variant="secondary" class="w-full">Филтрирай</x-ui.button>
            </div>
        </form>

        @if ($providers->isEmpty())
            <div class="mt-10">
                <x-ui.empty title="Няма намерени доставчици" description="Опитай с друг филтър или изпрати заявка — ще потърсим и в съседните райони.">
                    <x-ui.button href="{{ route('leads.create') }}">Изпрати заявка</x-ui.button>
                </x-ui.empty>
            </div>
        @else
            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($providers as $provider)
                    @include('tehnika.partials.provider-card', ['provider' => $provider])
                @endforeach
            </div>

            <div class="mt-10">{{ $providers->links() }}</div>
        @endif
    </div>
</x-layouts.app>
