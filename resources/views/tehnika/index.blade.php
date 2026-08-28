<x-layouts.app
    title="Категории техника под наем"
    description="Мини багери, автовишки, самосвали, товарачи, платформи и генератори под наем в цяла България. Опиши задачата си и ще намерим подходящите доставчици."
    :breadcrumbs="[
        ['label' => 'Начало', 'url' => route('home')],
        ['label' => 'Техника'],
    ]"
>
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <header class="max-w-3xl">
            <h1 class="text-4xl font-bold tracking-tight">Категории техника под наем</h1>
            <p class="mt-4 text-lg text-ink-600">
                Избери категория, за да научиш повече и да заявиш техника. Ако не си сигурен какво ти трябва —
                просто опиши задачата и ние ще се погрижим.
            </p>
            <div class="mt-6">
                <x-ui.button href="{{ route('leads.create') }}" size="lg">Намери техника</x-ui.button>
            </div>
        </header>

        <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($categories as $category)
                <article class="flex flex-col rounded-xl border border-ink-200 bg-white p-6 shadow-sm">
                    <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-brand-50 text-xl" aria-hidden="true">{{ $category->icon ?: '🚜' }}</span>
                    <h2 class="mt-4 text-xl font-semibold">
                        <a href="{{ route('categories.show', $category) }}" class="hover:text-brand-700">{{ $category->name }}</a>
                    </h2>
                    <p class="mt-2 flex-1 text-sm text-ink-500">{{ $category->description }}</p>

                    @if ($category->children->isNotEmpty())
                        <ul class="mt-4 flex flex-wrap gap-1.5">
                            @foreach ($category->children as $child)
                                <li><x-ui.badge>{{ $child->name }}</x-ui.badge></li>
                            @endforeach
                        </ul>
                    @endif

                    <a href="{{ route('categories.show', $category) }}" class="mt-5 text-sm font-semibold text-brand-700 hover:text-brand-800">
                        Научи повече →
                    </a>
                </article>
            @endforeach
        </div>
    </div>
</x-layouts.app>
