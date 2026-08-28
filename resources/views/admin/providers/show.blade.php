<x-layouts.dashboard :title="$provider->company_name" area="admin">
    <div class="mb-6">
        <a href="{{ route('admin.providers.index') }}" class="text-sm text-ink-500 hover:text-ink-800">← Всички доставчици</a>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight sm:text-3xl">{{ $provider->company_name }}</h1>
        <p class="mt-1 text-sm text-ink-500">
            {{ $provider->locationLabel() }} · {{ $provider->phone }} · {{ $provider->email }}
        </p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat label="Получени заявки" :value="$metrics['leads']" />
        <x-ui.stat label="Приети заявки" :value="$metrics['accepted']" />
        <x-ui.stat label="Conversion rate" :value="$metrics['conversion'].'%'" accent />
        <x-ui.stat label="Приходи" :value="number_format($metrics['revenue'], 2, ',', ' ').' лв.'"
                   :hint="'чакащи: '.number_format($metrics['pending_revenue'], 2, ',', ' ').' лв.'" />
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-ui.card>
                <h2 class="text-lg font-semibold">Профил</h2>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div><dt class="text-sm text-ink-500">ЕИК</dt><dd class="mt-0.5 text-ink-900">{{ $provider->eik ?: '—' }}</dd></div>
                    <div><dt class="text-sm text-ink-500">Лице за контакт</dt><dd class="mt-0.5 text-ink-900">{{ $provider->contact_name ?: '—' }}</dd></div>
                    <div><dt class="text-sm text-ink-500">Радиус</dt><dd class="mt-0.5 text-ink-900">{{ $provider->service_radius }} км</dd></div>
                    <div><dt class="text-sm text-ink-500">Уебсайт</dt><dd class="mt-0.5 text-ink-900">{{ $provider->website ?: '—' }}</dd></div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm text-ink-500">Обслужвани градове</dt>
                        <dd class="mt-0.5 text-ink-900">{{ $provider->serviceAreas->pluck('city.name')->filter()->implode(', ') ?: '—' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm text-ink-500">Описание</dt>
                        <dd class="mt-0.5 whitespace-pre-line text-ink-700">{{ $provider->description ?: '—' }}</dd>
                    </div>
                </dl>
            </x-ui.card>

            <x-ui.card>
                <h2 class="text-lg font-semibold">Техника ({{ $provider->equipment->count() }})</h2>
                @if ($provider->equipment->isEmpty())
                    <p class="mt-3 text-sm text-ink-500">Няма добавена техника.</p>
                @else
                    <ul class="mt-4 divide-y divide-ink-100">
                        @foreach ($provider->equipment as $item)
                            <li class="flex items-center justify-between gap-3 py-3">
                                <div>
                                    <p class="font-medium text-ink-900">{{ $item->title() }}</p>
                                    <p class="text-sm text-ink-500">
                                        {{ $item->operator_available ? 'с оператор' : 'без оператор' }}
                                        {{ $item->priceLabel() ? '· '.$item->priceLabel() : '' }}
                                    </p>
                                </div>
                                <x-ui.badge :color="$item->active ? 'bg-emerald-50 text-emerald-800 ring-emerald-200' : 'bg-ink-100 text-ink-600 ring-ink-200'">
                                    {{ $item->active ? 'Активна' : 'Скрита' }}
                                </x-ui.badge>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>

            <x-ui.card>
                <h2 class="text-lg font-semibold">Последни заявки</h2>
                @if ($assignments->isEmpty())
                    <p class="mt-3 text-sm text-ink-500">Още не са изпращани заявки.</p>
                @else
                    <ul class="mt-4 divide-y divide-ink-100">
                        @foreach ($assignments as $assignment)
                            <li class="flex items-center justify-between gap-3 py-3">
                                <div class="min-w-0">
                                    <a href="{{ route('admin.leads.show', $assignment->lead) }}" class="font-medium text-ink-900 hover:text-brand-700">
                                        {{ $assignment->lead->reference }}
                                    </a>
                                    <p class="text-sm text-ink-500">
                                        {{ $assignment->lead->categoryLabel() }} · {{ $assignment->sent_at?->format('d.m.Y') ?? '—' }}
                                    </p>
                                </div>
                                <x-ui.badge :color="$assignment->status->badgeClasses()">{{ $assignment->status->label() }}</x-ui.badge>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>
        </div>

        <div class="space-y-6">
            <x-ui.card>
                <h2 class="text-lg font-semibold">Модерация</h2>
                <div class="mt-4 space-y-3">
                    @unless ($provider->isApproved())
                        <form method="POST" action="{{ route('admin.providers.update', $provider) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="action" value="approve">
                            <x-ui.button variant="success" class="w-full">Одобри профила</x-ui.button>
                        </form>
                    @endunless

                    <form method="POST" action="{{ route('admin.providers.update', $provider) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="action" value="{{ $provider->verified ? 'unverify' : 'verify' }}">
                        <x-ui.button variant="outline" class="w-full">
                            {{ $provider->verified ? 'Премахни „Проверен“' : 'Маркирай като проверен' }}
                        </x-ui.button>
                    </form>

                    <form method="POST" action="{{ route('admin.providers.update', $provider) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="action" value="toggle_active">
                        <x-ui.button variant="outline" class="w-full">
                            {{ $provider->active ? 'Деактивирай' : 'Активирай' }}
                        </x-ui.button>
                    </form>

                    @if ($provider->isBlocked())
                        <form method="POST" action="{{ route('admin.providers.update', $provider) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="action" value="unblock">
                            <x-ui.button variant="outline" class="w-full">Отблокирай</x-ui.button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.providers.update', $provider) }}" class="space-y-3">
                            @csrf @method('PATCH')
                            <input type="hidden" name="action" value="block">
                            <x-ui.input name="reason" label="Причина за блокиране" />
                            <x-ui.button variant="danger" class="w-full">Блокирай</x-ui.button>
                        </form>
                    @endif
                </div>
            </x-ui.card>

            <x-ui.card>
                <h2 class="text-lg font-semibold">Нива на проверка</h2>
                <form method="POST" action="{{ route('admin.providers.update', $provider) }}" class="mt-4 space-y-3">
                    @csrf @method('PATCH')
                    <input type="hidden" name="action" value="verification">

                    @foreach ([
                        'email_verified' => 'Потвърден имейл',
                        'phone_verified' => 'Потвърден телефон',
                        'company_verified' => 'Потвърдена фирма',
                    ] as $field => $label)
                        <label class="flex items-center gap-3">
                            <input type="checkbox" name="{{ $field }}" value="1" @checked($provider->$field)
                                   class="h-5 w-5 rounded border-ink-300 text-brand-600 focus:ring-brand-600">
                            <span class="text-sm text-ink-700">{{ $label }}</span>
                        </label>
                    @endforeach

                    <x-ui.button class="w-full">Запази</x-ui.button>
                </form>
            </x-ui.card>
        </div>
    </div>
</x-layouts.dashboard>
