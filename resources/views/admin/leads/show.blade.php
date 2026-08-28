<x-layouts.dashboard :title="'Заявка '.$lead->reference" area="admin">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('admin.leads.index') }}" class="text-sm text-ink-500 hover:text-ink-800">← Всички заявки</a>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight sm:text-3xl">Заявка № {{ $lead->reference }}</h1>
            <p class="mt-1 text-sm text-ink-500">Създадена на {{ $lead->created_at->format('d.m.Y, H:i') }} ч.</p>
        </div>
        <x-ui.badge :color="$lead->status->badgeClasses()">{{ $lead->status->label() }}</x-ui.badge>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-ui.card>
                <h2 class="text-lg font-semibold">Задачата</h2>
                <p class="mt-3 whitespace-pre-line text-ink-700">{{ $lead->description }}</p>

                @if ($lead->images->isNotEmpty())
                    <div class="mt-5 grid grid-cols-3 gap-3">
                        @foreach ($lead->images as $image)
                            <a href="{{ $image->url() }}" target="_blank" rel="noopener">
                                <img src="{{ $image->url() }}" alt="Снимка на обекта" loading="lazy"
                                     class="h-28 w-full rounded-lg object-cover ring-1 ring-ink-200">
                            </a>
                        @endforeach
                    </div>
                @endif

                <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm text-ink-500">Техника</dt>
                        <dd class="mt-0.5 font-medium text-ink-900">
                            {{ $lead->categoryLabel() }}{{ $lead->equipment_type ? ' — '.$lead->equipment_type : '' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-ink-500">Локация</dt>
                        <dd class="mt-0.5 font-medium text-ink-900">
                            {{ $lead->locationLabel() }}{{ $lead->address ? ', '.$lead->address : '' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-ink-500">Дата</dt>
                        <dd class="mt-0.5 font-medium text-ink-900">{{ $lead->dateLabel() }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-ink-500">Продължителност</dt>
                        <dd class="mt-0.5 font-medium text-ink-900">{{ $lead->duration->label() }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-ink-500">Оператор</dt>
                        <dd class="mt-0.5 font-medium text-ink-900">{{ $lead->operator_required->label() }}</dd>
                    </div>
                    @foreach ($lead->attributeLines() as $label => $value)
                        <div>
                            <dt class="text-sm text-ink-500">{{ $label }}</dt>
                            <dd class="mt-0.5 font-medium text-ink-900">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </x-ui.card>

            {{-- Подходящи доставчици — ръчният избор на администратора --}}
            <x-ui.card>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold">Подходящи доставчици</h2>
                        <p class="mt-1 text-sm text-ink-500">
                            Подредени по резултат от matching алгоритъма. Избери към кои да изпратим заявката.
                        </p>
                    </div>
                </div>

                @if ($matches->isEmpty())
                    <div class="mt-5">
                        <x-ui.empty title="Няма подходящи доставчици"
                                    description="Провери дали има одобрени доставчици с активна техника в тази категория." />
                    </div>
                @else
                    <form method="POST" action="{{ route('admin.leads.send', $lead) }}" class="mt-5">
                        @csrf
                        <ul class="space-y-3">
                            @foreach ($matches as $match)
                                @php $sent = in_array($match->provider->id, $alreadySent, true); @endphp
                                <li class="rounded-xl border p-4 {{ $sent ? 'border-ink-200 bg-ink-50' : 'border-ink-200 bg-white' }}">
                                    <label class="flex items-start gap-3 {{ $sent ? 'cursor-default' : 'cursor-pointer' }}">
                                        <input type="checkbox" name="providers[]" value="{{ $match->provider->id }}"
                                               @disabled($sent)
                                               class="mt-1 h-5 w-5 rounded border-ink-300 text-brand-600 focus:ring-brand-600">
                                        <span class="min-w-0 flex-1">
                                            <span class="flex flex-wrap items-center gap-2">
                                                <a href="{{ route('admin.providers.show', $match->provider) }}"
                                                   class="font-semibold text-ink-900 hover:text-brand-700">
                                                    {{ $match->provider->company_name }}
                                                </a>
                                                @if ($match->provider->verified)
                                                    <x-ui.badge color="bg-emerald-50 text-emerald-800 ring-emerald-200">Проверен</x-ui.badge>
                                                @endif
                                                @if ($sent)
                                                    <x-ui.badge color="bg-sky-50 text-sky-800 ring-sky-200">Вече изпратена</x-ui.badge>
                                                @endif
                                                <span class="ml-auto shrink-0 rounded-full bg-brand-50 px-2.5 py-1 text-xs font-bold text-brand-800">
                                                    {{ $match->score }} т.
                                                </span>
                                            </span>

                                            <span class="mt-1 block text-sm text-ink-500">
                                                {{ $match->provider->locationLabel() }}
                                                @if ($match->distanceLabel())
                                                    · {{ $match->distanceLabel() }} от обекта
                                                @endif
                                                · тел. {{ $match->provider->phone }}
                                            </span>

                                            @if ($match->reasons)
                                                <span class="mt-2 flex flex-wrap gap-1.5">
                                                    @foreach ($match->reasons as $reason)
                                                        <x-ui.badge>{{ $reason }}</x-ui.badge>
                                                    @endforeach
                                                </span>
                                            @endif
                                        </span>
                                    </label>
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-5">
                            <x-ui.button size="lg">Изпрати заявката</x-ui.button>
                            <x-ui.error name="providers" />
                        </div>
                    </form>
                @endif
            </x-ui.card>

            @if ($lead->assignments->isNotEmpty())
                <x-ui.card>
                    <h2 class="text-lg font-semibold">История на изпращането</h2>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-ink-200 text-sm">
                            <thead class="text-left text-xs font-semibold uppercase tracking-wide text-ink-500">
                                <tr>
                                    <th class="py-2 pr-4">Доставчик</th>
                                    <th class="py-2 pr-4">Изпратена</th>
                                    <th class="py-2 pr-4">Прегледана</th>
                                    <th class="py-2 pr-4">Резултат</th>
                                    <th class="py-2">Статус</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-ink-100">
                                @foreach ($lead->assignments as $assignment)
                                    <tr>
                                        <td class="py-2.5 pr-4">
                                            <a href="{{ route('admin.providers.show', $assignment->providerProfile) }}"
                                               class="font-medium text-ink-900 hover:text-brand-700">
                                                {{ $assignment->providerProfile->company_name }}
                                            </a>
                                        </td>
                                        <td class="py-2.5 pr-4 text-ink-600">{{ $assignment->sent_at?->format('d.m.Y H:i') ?? '—' }}</td>
                                        <td class="py-2.5 pr-4 text-ink-600">{{ $assignment->viewed_at?->format('d.m.Y H:i') ?? '—' }}</td>
                                        <td class="py-2.5 pr-4 text-ink-600">{{ $assignment->match_score ?? '—' }}</td>
                                        <td class="py-2.5">
                                            <x-ui.badge :color="$assignment->status->badgeClasses()">{{ $assignment->status->label() }}</x-ui.badge>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-ui.card>
            @endif
        </div>

        <div class="space-y-6">
            <x-ui.card>
                <h2 class="text-lg font-semibold">Клиент</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div>
                        <dt class="text-ink-500">Име</dt>
                        <dd class="mt-0.5 font-medium text-ink-900">{{ $lead->contact_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-ink-500">Телефон</dt>
                        <dd class="mt-0.5"><a href="tel:{{ $lead->contact_phone }}" class="font-semibold text-brand-700 hover:underline">{{ $lead->contact_phone }}</a></dd>
                    </div>
                    <div>
                        <dt class="text-ink-500">Имейл</dt>
                        <dd class="mt-0.5"><a href="mailto:{{ $lead->contact_email }}" class="font-medium text-brand-700 hover:underline">{{ $lead->contact_email }}</a></dd>
                    </div>
                    <div>
                        <dt class="text-ink-500">IP адрес</dt>
                        <dd class="mt-0.5 text-ink-600">{{ $lead->ip_address ?? '—' }}</dd>
                    </div>
                </dl>
            </x-ui.card>

            <x-ui.card>
                <h2 class="text-lg font-semibold">Управление</h2>
                <form method="POST" action="{{ route('admin.leads.update', $lead) }}" class="mt-4 space-y-4">
                    @csrf @method('PATCH')
                    <x-ui.select name="status" label="Статус" :options="$statuses" :value="$lead->status->value" required />
                    <x-ui.input name="price" type="number" step="0.01" label="Цена на заявката (лв.)" :value="$lead->price ?? $lead->leadPrice()" />
                    <x-ui.textarea name="admin_notes" label="Вътрешни бележки" rows="4" :value="$lead->admin_notes" />
                    <x-ui.button class="w-full">Запази</x-ui.button>
                </form>
            </x-ui.card>
        </div>
    </div>
</x-layouts.dashboard>
