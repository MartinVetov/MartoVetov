@php
    use App\Enums\LeadProviderStatus;
    $lead = $assignment->lead;
    $accepted = in_array($assignment->status, [
        LeadProviderStatus::Accepted, LeadProviderStatus::Contacted, LeadProviderStatus::Completed,
    ], true);
@endphp

<x-layouts.dashboard :title="'Заявка '.$lead->reference">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('provider.leads.index') }}" class="text-sm text-ink-500 hover:text-ink-800">← Всички заявки</a>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight sm:text-3xl">Заявка № {{ $lead->reference }}</h1>
        </div>
        <x-ui.badge :color="$assignment->status->badgeClasses()">{{ $assignment->status->label() }}</x-ui.badge>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-ui.card>
                <h2 class="text-lg font-semibold">Какво трябва да бъде свършено</h2>
                <p class="mt-3 whitespace-pre-line text-ink-700">{{ $lead->description }}</p>

                @if ($lead->images->isNotEmpty())
                    <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach ($lead->images as $image)
                            <a href="{{ $image->url() }}" target="_blank" rel="noopener">
                                <img src="{{ $image->url() }}" alt="Снимка на обекта" loading="lazy"
                                     class="h-32 w-full rounded-lg object-cover ring-1 ring-ink-200">
                            </a>
                        @endforeach
                    </div>
                @endif
            </x-ui.card>

            <x-ui.card>
                <h2 class="text-lg font-semibold">Детайли</h2>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm text-ink-500">Техника</dt>
                        <dd class="mt-0.5 font-medium text-ink-900">
                            {{ $lead->categoryLabel() }}{{ $lead->equipment_type ? ' — '.$lead->equipment_type : '' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-ink-500">Локация</dt>
                        <dd class="mt-0.5 font-medium text-ink-900">{{ $lead->locationLabel() }}</dd>
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
        </div>

        <div class="space-y-6">
            <x-ui.card>
                <h2 class="text-lg font-semibold">Данни за контакт</h2>

                @if ($accepted)
                    <dl class="mt-4 space-y-3">
                        <div>
                            <dt class="text-sm text-ink-500">Клиент</dt>
                            <dd class="mt-0.5 font-medium text-ink-900">{{ $lead->contact_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-ink-500">Телефон</dt>
                            <dd class="mt-0.5"><a href="tel:{{ $lead->contact_phone }}" class="font-semibold text-brand-700 hover:underline">{{ $lead->contact_phone }}</a></dd>
                        </div>
                        <div>
                            <dt class="text-sm text-ink-500">Имейл</dt>
                            <dd class="mt-0.5"><a href="mailto:{{ $lead->contact_email }}" class="font-medium text-brand-700 hover:underline">{{ $lead->contact_email }}</a></dd>
                        </div>
                        @if ($lead->address)
                            <div>
                                <dt class="text-sm text-ink-500">Адрес</dt>
                                <dd class="mt-0.5 font-medium text-ink-900">{{ $lead->address }}</dd>
                            </div>
                        @endif
                    </dl>
                @else
                    <p class="mt-3 text-sm text-ink-500">
                        Данните за контакт се отключват, след като приемеш заявката.
                    </p>
                    <p class="mt-3 text-sm text-ink-600">
                        Цена на заявката:
                        <strong class="text-ink-900">{{ number_format((float) ($assignment->price ?? $lead->leadPrice()), 2, ',', ' ') }} лв.</strong>
                    </p>
                @endif
            </x-ui.card>

            <x-ui.card>
                <h2 class="text-lg font-semibold">Действия</h2>
                <div class="mt-4 space-y-3">
                    @if ($assignment->status === LeadProviderStatus::Sent || $assignment->status === LeadProviderStatus::Viewed)
                        <form method="POST" action="{{ route('provider.leads.update', $assignment) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="action" value="accept">
                            <x-ui.button variant="success" class="w-full">Приеми заявката</x-ui.button>
                        </form>

                        <form method="POST" action="{{ route('provider.leads.update', $assignment) }}"
                              x-data="{ open: false }">
                            @csrf @method('PATCH')
                            <input type="hidden" name="action" value="decline">
                            <x-ui.button type="button" variant="outline" class="w-full" @click="open = !open">Откажи</x-ui.button>
                            <div x-show="open" x-cloak class="mt-3 space-y-3">
                                <x-ui.textarea name="notes" label="Причина (по желание)" rows="3" />
                                <x-ui.button variant="danger" class="w-full">Потвърди отказа</x-ui.button>
                            </div>
                        </form>
                    @elseif ($assignment->status === LeadProviderStatus::Accepted)
                        <form method="POST" action="{{ route('provider.leads.update', $assignment) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="action" value="contacted">
                            <x-ui.button class="w-full">Свързах се с клиента</x-ui.button>
                        </form>
                    @elseif ($assignment->status === LeadProviderStatus::Contacted)
                        <form method="POST" action="{{ route('provider.leads.update', $assignment) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="action" value="complete">
                            <x-ui.button variant="success" class="w-full">Отбележи като завършена</x-ui.button>
                        </form>
                    @else
                        <p class="text-sm text-ink-500">Заявката е приключена. Няма налични действия.</p>
                    @endif
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.dashboard>
