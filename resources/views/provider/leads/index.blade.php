@php use App\Enums\LeadProviderStatus; @endphp

<x-layouts.dashboard title="Моите заявки" heading="Моите заявки">
    <form method="GET" class="mb-6 flex flex-wrap gap-3">
        <div class="w-full sm:w-64">
            <x-ui.select name="status" label="Статус" placeholder="Всички статуси"
                         :options="collect(LeadProviderStatus::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])"
                         :value="request('status')" />
        </div>
        <div class="flex items-end">
            <x-ui.button variant="secondary">Филтрирай</x-ui.button>
        </div>
    </form>

    @if ($assignments->isEmpty())
        <x-ui.empty title="Няма заявки" description="Когато получиш заявка, ще я видиш тук и ще ти изпратим имейл." />
    @else
        {{-- На мобилни устройства показваме карти вместо широка таблица. --}}
        <div class="space-y-3 lg:hidden">
            @foreach ($assignments as $assignment)
                <a href="{{ route('provider.leads.show', $assignment) }}" class="block rounded-xl border border-ink-200 bg-white p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-medium text-ink-900">{{ $assignment->lead->categoryLabel() }}</p>
                            <p class="mt-0.5 text-sm text-ink-500">{{ $assignment->lead->locationLabel() }}</p>
                        </div>
                        <x-ui.badge :color="$assignment->status->badgeClasses()">{{ $assignment->status->label() }}</x-ui.badge>
                    </div>
                    <dl class="mt-3 grid grid-cols-2 gap-2 text-sm">
                        <div>
                            <dt class="text-ink-400">Получена</dt>
                            <dd class="text-ink-700">{{ $assignment->sent_at?->format('d.m.Y') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-ink-400">За дата</dt>
                            <dd class="text-ink-700">{{ $assignment->lead->dateLabel() }}</dd>
                        </div>
                    </dl>
                </a>
            @endforeach
        </div>

        <div class="hidden overflow-hidden rounded-xl border border-ink-200 bg-white lg:block">
            <table class="min-w-full divide-y divide-ink-200 text-sm">
                <thead class="bg-ink-50 text-left text-xs font-semibold uppercase tracking-wide text-ink-500">
                    <tr>
                        <th class="px-4 py-3">Получена</th>
                        <th class="px-4 py-3">Категория</th>
                        <th class="px-4 py-3">Град</th>
                        <th class="px-4 py-3">Дата на услугата</th>
                        <th class="px-4 py-3">Оператор</th>
                        <th class="px-4 py-3">Статус</th>
                        <th class="px-4 py-3"><span class="sr-only">Действие</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @foreach ($assignments as $assignment)
                        <tr class="hover:bg-ink-50">
                            <td class="px-4 py-3 text-ink-600">{{ $assignment->sent_at?->format('d.m.Y') ?? '—' }}</td>
                            <td class="px-4 py-3 font-medium text-ink-900">{{ $assignment->lead->categoryLabel() }}</td>
                            <td class="px-4 py-3 text-ink-600">{{ $assignment->lead->city?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-ink-600">{{ $assignment->lead->dateLabel() }}</td>
                            <td class="px-4 py-3 text-ink-600">{{ $assignment->lead->operator_required->shortLabel() }}</td>
                            <td class="px-4 py-3">
                                <x-ui.badge :color="$assignment->status->badgeClasses()">{{ $assignment->status->label() }}</x-ui.badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('provider.leads.show', $assignment) }}" class="font-semibold text-brand-700 hover:underline">Отвори</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $assignments->links() }}</div>
    @endif
</x-layouts.dashboard>
