<x-layouts.dashboard title="Категории" heading="Категории техника" area="admin">
    <div class="mb-6 flex justify-end">
        <x-ui.button href="{{ route('admin.categories.create') }}">Нова категория</x-ui.button>
    </div>

    <div class="space-y-4">
        @foreach ($categories as $category)
            <x-ui.card>
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="text-lg font-semibold">
                            {{ $category->icon }} {{ $category->name }}
                            <span class="ml-1 text-sm font-normal text-ink-400">/tehnika/{{ $category->slug }}</span>
                        </h2>
                        <p class="mt-1 text-sm text-ink-500">
                            {{ $category->equipment_count }} машини · {{ $category->leads_count }} заявки ·
                            цена на заявка: {{ number_format($category->leadPrice(), 2, ',', ' ') }} лв.
                        </p>
                        @if ($category->children->isNotEmpty())
                            <ul class="mt-3 flex flex-wrap gap-1.5">
                                @foreach ($category->children as $child)
                                    <li>
                                        <a href="{{ route('admin.categories.edit', $child) }}">
                                            <x-ui.badge>{{ $child->name }}</x-ui.badge>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        <x-ui.badge :color="$category->active ? 'bg-emerald-50 text-emerald-800 ring-emerald-200' : 'bg-ink-100 text-ink-600 ring-ink-200'">
                            {{ $category->active ? 'Активна' : 'Изключена' }}
                        </x-ui.badge>
                        <x-ui.button href="{{ route('admin.categories.edit', $category) }}" size="sm" variant="outline">Редактирай</x-ui.button>
                    </div>
                </div>
            </x-ui.card>
        @endforeach
    </div>
</x-layouts.dashboard>
