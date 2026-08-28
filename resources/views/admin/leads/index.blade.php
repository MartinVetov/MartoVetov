<x-layouts.dashboard title="Заявки" heading="Заявки" area="admin">
    <form method="GET" class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        <x-ui.input name="tarsene" label="Търсене" :value="request('tarsene')" placeholder="Номер, име, телефон…" />
        <x-ui.select name="status" label="Статус" placeholder="Всички" :options="$statuses" :value="request('status')" />
        <x-ui.select name="kategoriya" label="Категория" placeholder="Всички"
                     :options="$categories->pluck('name', 'id')" :value="request('kategoriya')" />
        <x-ui.select name="grad" label="Град" placeholder="Всички"
                     :options="$cities->pluck('name', 'id')" :value="request('grad')" />
        <div class="flex items-end gap-2">
            <x-ui.button variant="secondary">Филтрирай</x-ui.button>
            <x-ui.button href="{{ route('admin.leads.index') }}" variant="ghost">Изчисти</x-ui.button>
        </div>
    </form>

    @if ($leads->isEmpty())
        <x-ui.empty title="Няма заявки по този филтър" />
    @else
        <div class="space-y-3 lg:hidden">
            @foreach ($leads as $lead)
                <a href="{{ route('admin.leads.show', $lead) }}" class="block rounded-xl border border-ink-200 bg-white p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-medium text-ink-900">{{ $lead->reference }}</p>
                            <p class="mt-0.5 text-sm text-ink-500">{{ $lead->categoryLabel() }}</p>
                            <p class="text-sm text-ink-500">{{ $lead->city?->name ?? '—' }} · {{ $lead->created_at->format('d.m.Y') }}</p>
                        </div>
                        <x-ui.badge :color="$lead->status->badgeClasses()">{{ $lead->status->label() }}</x-ui.badge>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="hidden overflow-x-auto rounded-xl border border-ink-200 bg-white lg:block">
            <table class="min-w-full divide-y divide-ink-200 text-sm">
                <thead class="bg-ink-50 text-left text-xs font-semibold uppercase tracking-wide text-ink-500">
                    <tr>
                        <th class="px-4 py-3">№</th>
                        <th class="px-4 py-3">Дата</th>
                        <th class="px-4 py-3">Клиент</th>
                        <th class="px-4 py-3">Телефон</th>
                        <th class="px-4 py-3">Категория</th>
                        <th class="px-4 py-3">Град</th>
                        <th class="px-4 py-3">За дата</th>
                        <th class="px-4 py-3">Оператор</th>
                        <th class="px-4 py-3">Изпратена</th>
                        <th class="px-4 py-3">Цена</th>
                        <th class="px-4 py-3">Статус</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @foreach ($leads as $lead)
                        <tr class="hover:bg-ink-50">
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.leads.show', $lead) }}" class="font-semibold text-brand-700 hover:underline">
                                    {{ $lead->reference }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-ink-600">{{ $lead->created_at->format('d.m.Y') }}</td>
                            <td class="px-4 py-3 text-ink-700">{{ $lead->contact_name }}</td>
                            <td class="px-4 py-3 text-ink-600">{{ $lead->contact_phone }}</td>
                            <td class="px-4 py-3 text-ink-600">{{ $lead->categoryLabel() }}</td>
                            <td class="px-4 py-3 text-ink-600">{{ $lead->city?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-ink-600">{{ $lead->dateLabel() }}</td>
                            <td class="px-4 py-3 text-ink-600">{{ $lead->operator_required->shortLabel() }}</td>
                            <td class="px-4 py-3 text-ink-600">{{ $lead->assignments_count }} дост.</td>
                            <td class="px-4 py-3 text-ink-600">{{ number_format($lead->leadPrice(), 2, ',', ' ') }} лв.</td>
                            <td class="px-4 py-3">
                                <x-ui.badge :color="$lead->status->badgeClasses()">{{ $lead->status->label() }}</x-ui.badge>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $leads->links() }}</div>
    @endif
</x-layouts.dashboard>
