@php
    use App\Enums\PriceUnit;
    $isEdit = isset($equipment) && $equipment->exists;
    $selectedCategoryId = old('equipment_category_id', $isEdit ? $equipment->equipment_category_id : null);
    $typeMap = $categories->mapWithKeys(fn ($c) => [$c->id => $c->children->pluck('name')->values()]);
@endphp

<form method="POST"
      action="{{ $isEdit ? route('provider.equipment.update', $equipment) : route('provider.equipment.store') }}"
      enctype="multipart/form-data"
      class="space-y-6"
      x-data="{
          categoryId: {{ (int) $selectedCategoryId }},
          types: {{ Js::from($typeMap) }},
          get currentTypes() { return this.types[this.categoryId] ?? []; },
      }">
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <x-ui.card>
        <h2 class="text-lg font-semibold">Основни данни</h2>

        <div class="mt-5 grid gap-5 sm:grid-cols-2">
            <div>
                <label for="equipment_category_id" class="mb-1.5 block text-sm font-medium text-ink-800">
                    Категория <span class="text-brand-600" aria-hidden="true">*</span>
                </label>
                <select name="equipment_category_id" id="equipment_category_id" required x-model.number="categoryId"
                        class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-base text-ink-900 shadow-sm ring-1 ring-inset ring-ink-300 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm">
                    <option value="">Избери категория</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((int) $selectedCategoryId === $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <x-ui.error name="equipment_category_id" />
            </div>

            <div>
                <label for="type" class="mb-1.5 block text-sm font-medium text-ink-800">Тип / размер</label>
                <select name="type" id="type"
                        class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-base text-ink-900 shadow-sm ring-1 ring-inset ring-ink-300 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm">
                    <option value="">Не е уточнен</option>
                    <template x-for="type in currentTypes" :key="type">
                        <option :value="type" x-text="type"
                                :selected="type === @js(old('type', $isEdit ? $equipment->type : ''))"></option>
                    </template>
                </select>
                <x-ui.error name="type" />
            </div>

            <x-ui.input name="brand" label="Марка" :value="$isEdit ? $equipment->brand : null" placeholder="напр. Caterpillar" />
            <x-ui.input name="model" label="Модел" :value="$isEdit ? $equipment->model : null" placeholder="напр. 302.7 CR" />
            <x-ui.input name="year" type="number" label="Година" :value="$isEdit ? $equipment->year : null" min="1960" max="{{ date('Y') + 1 }}" />
            <x-ui.input name="weight" type="number" step="0.1" label="Тегло (тонове)" :value="$isEdit ? $equipment->weight : null" />
        </div>

        <div class="mt-5">
            <x-ui.textarea name="description" label="Описание" rows="4"
                           :value="$isEdit ? $equipment->description : null"
                           placeholder="Прикачени инструменти, специфики, ограничения за достъп…" />
        </div>
    </x-ui.card>

    <x-ui.card>
        <h2 class="text-lg font-semibold">Оператор и цена</h2>

        <div class="mt-5 space-y-3">
            <label class="flex items-start gap-3">
                <input type="checkbox" name="operator_available" value="1"
                       @checked(old('operator_available', $isEdit ? $equipment->operator_available : true))
                       class="mt-0.5 h-5 w-5 rounded border-ink-300 text-brand-600 focus:ring-brand-600">
                <span class="text-sm text-ink-700">Предлагам техниката с оператор</span>
            </label>

            <label class="flex items-start gap-3">
                <input type="checkbox" name="operator_only" value="1"
                       @checked(old('operator_only', $isEdit ? $equipment->operator_only : false))
                       class="mt-0.5 h-5 w-5 rounded border-ink-300 text-brand-600 focus:ring-brand-600">
                <span class="text-sm text-ink-700">Само с оператор (не отдавам техниката без оператор)</span>
            </label>
        </div>

        <div class="mt-5 grid gap-5 sm:grid-cols-3">
            <x-ui.input name="price_from" type="number" step="0.01" label="Цена от (лв.)" :value="$isEdit ? $equipment->price_from : null" />
            <x-ui.select name="price_unit" label="Мерна единица" :options="PriceUnit::options()"
                         :value="$isEdit ? $equipment->price_unit->value : 'hour'" required />
            <x-ui.input name="min_duration" label="Минимално време" :value="$isEdit ? $equipment->min_duration : null" placeholder="напр. 4 часа" />
        </div>
    </x-ui.card>

    <x-ui.card>
        <h2 class="text-lg font-semibold">Снимки</h2>
        <p class="mt-1 text-sm text-ink-500">
            До {{ auth()->user()->providerProfile->planSetting('max_images_per_equipment') }} снимки по план
            {{ auth()->user()->providerProfile->plan()->label() }}.
        </p>

        @if ($isEdit && $equipment->images->isNotEmpty())
            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach ($equipment->images as $image)
                    <div class="relative">
                        <img src="{{ $image->url() }}" alt="{{ $equipment->title() }}" loading="lazy"
                             class="h-28 w-full rounded-lg object-cover ring-1 ring-ink-200">
                        <button type="button" form="delete-image-{{ $image->id }}"
                                class="absolute right-1.5 top-1.5 rounded-full bg-white/90 px-2 py-0.5 text-xs font-semibold text-rose-600 shadow">
                            Изтрий
                        </button>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mt-4">
            <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp"
                   class="block w-full rounded-lg border border-ink-300 bg-white p-2.5 text-sm text-ink-600 file:mr-3 file:rounded-md file:border-0 file:bg-ink-100 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-ink-700">
            <x-ui.error name="images.0" />
        </div>
    </x-ui.card>

    <x-ui.card>
        <label class="flex items-start gap-3">
            <input type="checkbox" name="active" value="1"
                   @checked(old('active', $isEdit ? $equipment->active : true))
                   class="mt-0.5 h-5 w-5 rounded border-ink-300 text-brand-600 focus:ring-brand-600">
            <span>
                <span class="block text-sm font-medium text-ink-800">Активна</span>
                <span class="mt-0.5 block text-sm text-ink-500">Само активната техника участва в подбора на заявки.</span>
            </span>
        </label>
    </x-ui.card>

    <div class="flex items-center gap-3">
        <x-ui.button size="lg">{{ $isEdit ? 'Запази промените' : 'Добави техниката' }}</x-ui.button>
        <x-ui.button href="{{ route('provider.equipment.index') }}" variant="ghost" size="lg">Отказ</x-ui.button>
    </div>
</form>

@if ($isEdit)
    @foreach ($equipment->images as $image)
        <form id="delete-image-{{ $image->id }}" method="POST"
              action="{{ route('provider.equipment.images.destroy', $image) }}" class="hidden">
            @csrf @method('DELETE')
        </form>
    @endforeach
@endif
