@php
    $maxDaily = max(1, max($leadsByDay ?: [0]));
@endphp

<x-layouts.dashboard title="Табло" heading="Табло" area="admin">
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat label="Заявки днес" :value="$stats['leads_today']" accent />
        <x-ui.stat label="Заявки този месец" :value="$stats['leads_month']" :hint="$stats['valid_month'].' валидни'" />
        <x-ui.stat label="Активни доставчици" :value="$stats['providers_active']" :hint="$stats['providers_new'].' нови този месец'" />
        <x-ui.stat label="Завършени заявки" :value="$stats['completed_month']" hint="този месец" />
        <x-ui.stat label="Приходи (платени)" :value="number_format($stats['revenue_month'], 2, ',', ' ').' лв.'" hint="този месец" />
        <x-ui.stat label="Приходи (чакащи)" :value="number_format($stats['revenue_pending'], 2, ',', ' ').' лв.'" />
        <x-ui.stat label="Conversion rate" :value="$stats['conversion_rate'].'%'" hint="приети от доставчик" />
        <x-ui.stat label="Чакат одобрение" :value="$stats['providers_pending']" hint="профили на доставчици" />
    </div>

    <section class="mt-8">
        <x-ui.card>
            <h2 class="text-lg font-semibold">Заявки по дни (последните 30 дни)</h2>
            <div class="mt-6 flex h-40 items-end gap-1 overflow-x-auto" role="img"
                 aria-label="Стълбовидна графика на заявките по дни за последните 30 дни">
                @foreach ($leadsByDay as $day => $count)
                    <div class="flex min-w-[10px] flex-1 flex-col items-center justify-end gap-1"
                         title="{{ \Illuminate\Support\Carbon::parse($day)->format('d.m.Y') }}: {{ $count }}">
                        <span class="text-[10px] text-ink-400">{{ $count ?: '' }}</span>
                        <div class="w-full rounded-t bg-brand-500"
                             style="height: {{ max(2, (int) round($count / $maxDaily * 110)) }}px"></div>
                    </div>
                @endforeach
            </div>
            <div class="mt-2 flex justify-between text-xs text-ink-400">
                <span>{{ \Illuminate\Support\Carbon::parse(array_key_first($leadsByDay))->format('d.m') }}</span>
                <span>{{ \Illuminate\Support\Carbon::parse(array_key_last($leadsByDay))->format('d.m') }}</span>
            </div>
        </x-ui.card>
    </section>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <x-ui.card>
            <h2 class="text-lg font-semibold">Заявки по категории (90 дни)</h2>
            @if ($leadsByCategory->isEmpty())
                <p class="mt-3 text-sm text-ink-500">Още няма данни.</p>
            @else
                <ul class="mt-4 space-y-3">
                    @foreach ($leadsByCategory as $row)
                        <li>
                            <div class="flex justify-between text-sm">
                                <span class="text-ink-700">{{ $row->category?->name ?? 'Не е посочена категория' }}</span>
                                <span class="font-semibold text-ink-900">{{ $row->total }}</span>
                            </div>
                            <div class="mt-1 h-2 rounded-full bg-ink-100">
                                <div class="h-2 rounded-full bg-brand-500"
                                     style="width: {{ round($row->total / max(1, $leadsByCategory->max('total')) * 100) }}%"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>

        <x-ui.card>
            <h2 class="text-lg font-semibold">Заявки по градове (90 дни)</h2>
            @if ($leadsByCity->isEmpty())
                <p class="mt-3 text-sm text-ink-500">Още няма данни.</p>
            @else
                <ul class="mt-4 space-y-3">
                    @foreach ($leadsByCity as $row)
                        <li>
                            <div class="flex justify-between text-sm">
                                <span class="text-ink-700">{{ $row->city?->name ?? 'Не е посочен град' }}</span>
                                <span class="font-semibold text-ink-900">{{ $row->total }}</span>
                            </div>
                            <div class="mt-1 h-2 rounded-full bg-ink-100">
                                <div class="h-2 rounded-full bg-ink-700"
                                     style="width: {{ round($row->total / max(1, $leadsByCity->max('total')) * 100) }}%"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <x-ui.card>
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold">Последни заявки</h2>
                <a href="{{ route('admin.leads.index') }}" class="text-sm font-semibold text-brand-700">Всички →</a>
            </div>
            <ul class="mt-4 divide-y divide-ink-100">
                @forelse ($latestLeads as $lead)
                    <li class="flex items-center justify-between gap-3 py-3">
                        <div class="min-w-0">
                            <a href="{{ route('admin.leads.show', $lead) }}" class="font-medium text-ink-900 hover:text-brand-700">
                                {{ $lead->reference }}
                            </a>
                            <p class="mt-0.5 truncate text-sm text-ink-500">
                                {{ $lead->categoryLabel() }} · {{ $lead->city?->name ?? '—' }}
                            </p>
                        </div>
                        <x-ui.badge :color="$lead->status->badgeClasses()">{{ $lead->status->label() }}</x-ui.badge>
                    </li>
                @empty
                    <li class="py-6 text-sm text-ink-500">Още няма заявки.</li>
                @endforelse
            </ul>
        </x-ui.card>

        <x-ui.card>
            <h2 class="text-lg font-semibold">Най-активни доставчици</h2>
            <ul class="mt-4 divide-y divide-ink-100">
                @forelse ($topProviders as $provider)
                    <li class="flex items-center justify-between gap-3 py-3">
                        <div class="min-w-0">
                            <a href="{{ route('admin.providers.show', $provider) }}" class="font-medium text-ink-900 hover:text-brand-700">
                                {{ $provider->company_name }}
                            </a>
                            <p class="mt-0.5 text-sm text-ink-500">{{ $provider->leads_count }} заявки</p>
                        </div>
                        <span class="text-sm font-semibold text-emerald-700">{{ $provider->accepted_count }} приети</span>
                    </li>
                @empty
                    <li class="py-6 text-sm text-ink-500">Още няма доставчици.</li>
                @endforelse
            </ul>
        </x-ui.card>
    </div>
</x-layouts.dashboard>
