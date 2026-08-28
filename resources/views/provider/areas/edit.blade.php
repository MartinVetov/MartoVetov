@php $citiesByRegion = $cities->groupBy('region'); @endphp

<x-layouts.dashboard title="Обслужвани райони" heading="Обслужвани райони">
    <p class="mb-6 max-w-2xl text-ink-600">
        Избери градовете, в които реално работиш. Заявките от тези населени места ще стигат до теб
        с по-висок приоритет.
    </p>

    <form method="POST" action="{{ route('provider.areas.update') }}" class="space-y-6">
        @csrf @method('PUT')

        <x-ui.card>
            <div class="sm:max-w-xs">
                <x-ui.input name="service_radius" type="number" label="Радиус на обслужване (км)" required
                            :value="$profile->service_radius" min="5" max="400"
                            hint="Използва се и за градове, които не си избрал изрично." />
            </div>
        </x-ui.card>

        <x-ui.card>
            <h2 class="text-lg font-semibold">Градове</h2>
            <div class="mt-5 space-y-6">
                @foreach ($citiesByRegion as $region => $regionCities)
                    <fieldset>
                        <legend class="text-sm font-semibold uppercase tracking-wide text-ink-500">Област {{ $region }}</legend>
                        <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($regionCities as $city)
                                <label class="flex items-center gap-3 rounded-lg border border-ink-200 px-3 py-2 transition has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50">
                                    <input type="checkbox" name="cities[]" value="{{ $city->id }}"
                                           @checked(in_array($city->id, old('cities', $selected)))
                                           class="h-5 w-5 rounded border-ink-300 text-brand-600 focus:ring-brand-600">
                                    <span class="text-sm text-ink-800">{{ $city->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endforeach
            </div>
        </x-ui.card>

        <x-ui.button size="lg">Запази районите</x-ui.button>
    </form>
</x-layouts.dashboard>
