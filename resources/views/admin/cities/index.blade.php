<x-layouts.dashboard title="Градове" heading="Градове" area="admin">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <form method="GET" class="flex items-end gap-2">
            <div class="w-64"><x-ui.input name="tarsene" label="Търсене" :value="request('tarsene')" /></div>
            <x-ui.button variant="secondary">Търси</x-ui.button>
        </form>
        <x-ui.button href="{{ route('admin.cities.create') }}">Нов град</x-ui.button>
    </div>

    <div class="overflow-x-auto rounded-xl border border-ink-200 bg-white">
        <table class="min-w-full divide-y divide-ink-200 text-sm">
            <thead class="bg-ink-50 text-left text-xs font-semibold uppercase tracking-wide text-ink-500">
                <tr>
                    <th class="px-4 py-3">Град</th>
                    <th class="px-4 py-3">Област</th>
                    <th class="px-4 py-3">Slug</th>
                    <th class="px-4 py-3">Доставчици</th>
                    <th class="px-4 py-3">Заявки</th>
                    <th class="px-4 py-3">SEO страница</th>
                    <th class="px-4 py-3"><span class="sr-only">Действие</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @foreach ($cities as $city)
                    <tr class="hover:bg-ink-50">
                        <td class="px-4 py-3 font-medium text-ink-900">{{ $city->name }}</td>
                        <td class="px-4 py-3 text-ink-600">{{ $city->region }}</td>
                        <td class="px-4 py-3 text-ink-400">{{ $city->slug }}</td>
                        <td class="px-4 py-3 text-ink-600">{{ $city->providers_count }}</td>
                        <td class="px-4 py-3 text-ink-600">{{ $city->leads_count }}</td>
                        <td class="px-4 py-3">
                            @if ($city->landing_enabled)
                                <x-ui.badge color="bg-emerald-50 text-emerald-800 ring-emerald-200">Включена</x-ui.badge>
                            @else
                                <x-ui.badge>Изключена</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.cities.edit', $city) }}" class="font-semibold text-brand-700 hover:underline">Редактирай</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $cities->links() }}</div>
</x-layouts.dashboard>
