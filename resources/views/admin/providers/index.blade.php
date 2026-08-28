<x-layouts.dashboard title="Доставчици" heading="Доставчици" area="admin">
    <form method="GET" class="mb-6 grid gap-3 sm:grid-cols-3">
        <x-ui.input name="tarsene" label="Търсене по фирма" :value="request('tarsene')" />
        <x-ui.select name="status" label="Състояние" placeholder="Всички" :value="request('status')"
                     :options="[
                        'pending' => 'Чакат одобрение',
                        'active' => 'Активни',
                        'verified' => 'Проверени',
                        'blocked' => 'Блокирани',
                     ]" />
        <div class="flex items-end gap-2">
            <x-ui.button variant="secondary">Филтрирай</x-ui.button>
            <x-ui.button href="{{ route('admin.providers.index') }}" variant="ghost">Изчисти</x-ui.button>
        </div>
    </form>

    @if ($providers->isEmpty())
        <x-ui.empty title="Няма доставчици по този филтър" />
    @else
        <div class="overflow-x-auto rounded-xl border border-ink-200 bg-white">
            <table class="min-w-full divide-y divide-ink-200 text-sm">
                <thead class="bg-ink-50 text-left text-xs font-semibold uppercase tracking-wide text-ink-500">
                    <tr>
                        <th class="px-4 py-3">Фирма</th>
                        <th class="px-4 py-3">Град</th>
                        <th class="px-4 py-3">Техника</th>
                        <th class="px-4 py-3">Заявки</th>
                        <th class="px-4 py-3">Състояние</th>
                        <th class="px-4 py-3"><span class="sr-only">Действие</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @foreach ($providers as $provider)
                        <tr class="hover:bg-ink-50">
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.providers.show', $provider) }}" class="font-semibold text-ink-900 hover:text-brand-700">
                                    {{ $provider->company_name }}
                                </a>
                                <p class="text-xs text-ink-500">{{ $provider->user?->email }}</p>
                            </td>
                            <td class="px-4 py-3 text-ink-600">{{ $provider->city?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-ink-600">{{ $provider->equipment_count }}</td>
                            <td class="px-4 py-3 text-ink-600">{{ $provider->leads_count }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1.5">
                                    @if ($provider->isBlocked())
                                        <x-ui.badge color="bg-rose-50 text-rose-800 ring-rose-200">Блокиран</x-ui.badge>
                                    @elseif (! $provider->isApproved())
                                        <x-ui.badge color="bg-amber-50 text-amber-800 ring-amber-200">Чака одобрение</x-ui.badge>
                                    @elseif ($provider->active)
                                        <x-ui.badge color="bg-emerald-50 text-emerald-800 ring-emerald-200">Активен</x-ui.badge>
                                    @else
                                        <x-ui.badge>Неактивен</x-ui.badge>
                                    @endif

                                    @if ($provider->verified)
                                        <x-ui.badge color="bg-sky-50 text-sky-800 ring-sky-200">Проверен</x-ui.badge>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.providers.show', $provider) }}" class="font-semibold text-brand-700 hover:underline">Отвори</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $providers->links() }}</div>
    @endif
</x-layouts.dashboard>
