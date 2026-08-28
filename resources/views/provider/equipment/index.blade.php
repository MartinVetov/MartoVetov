<x-layouts.dashboard title="Моята техника" heading="Моята техника">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-ink-500">
            {{ $equipment->count() }} от
            {{ $profile->planSetting('max_equipment') ?? 'неограничен брой' }} машини по план {{ $profile->plan()->label() }}.
        </p>
        <x-ui.button href="{{ route('provider.equipment.create') }}">Добави техника</x-ui.button>
    </div>

    @if ($equipment->isEmpty())
        <x-ui.empty title="Още нямаш добавена техника"
                    description="Добави машините си, за да получаваш подходящи заявки.">
            <x-ui.button href="{{ route('provider.equipment.create') }}">Добави първата машина</x-ui.button>
        </x-ui.empty>
    @else
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach ($equipment as $item)
                <x-ui.card>
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="font-semibold text-ink-900">{{ $item->title() }}</h2>
                            <p class="mt-0.5 text-sm text-ink-500">
                                {{ collect([
                                    $item->year ? $item->year.' г.' : null,
                                    $item->weight ? $item->weight.' т' : null,
                                    $item->operator_available ? 'с оператор' : 'без оператор',
                                ])->filter()->implode(' · ') }}
                            </p>
                        </div>
                        <x-ui.badge :color="$item->active ? 'bg-emerald-50 text-emerald-800 ring-emerald-200' : 'bg-ink-100 text-ink-600 ring-ink-200'">
                            {{ $item->active ? 'Активна' : 'Скрита' }}
                        </x-ui.badge>
                    </div>

                    @if ($item->images->isNotEmpty())
                        <div class="mt-4 flex gap-2 overflow-x-auto">
                            @foreach ($item->images as $image)
                                <img src="{{ $image->url() }}" alt="{{ $item->title() }}" loading="lazy"
                                     class="h-20 w-28 shrink-0 rounded-lg object-cover ring-1 ring-ink-200">
                            @endforeach
                        </div>
                    @endif

                    @if ($item->priceLabel())
                        <p class="mt-4 text-sm font-semibold text-brand-700">{{ $item->priceLabel() }}</p>
                    @endif

                    <div class="mt-5 flex items-center gap-3">
                        <x-ui.button href="{{ route('provider.equipment.edit', $item) }}" size="sm" variant="outline">Редактирай</x-ui.button>
                        <form method="POST" action="{{ route('provider.equipment.destroy', $item) }}"
                              onsubmit="return confirm('Наистина ли да премахнем тази техника?');">
                            @csrf @method('DELETE')
                            <x-ui.button size="sm" variant="ghost" class="text-rose-600 hover:bg-rose-50">Премахни</x-ui.button>
                        </form>
                    </div>
                </x-ui.card>
            @endforeach
        </div>
    @endif
</x-layouts.dashboard>
