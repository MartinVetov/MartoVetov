@php $isEdit = $city->exists; @endphp

<form method="POST" action="{{ $isEdit ? route('admin.cities.update', $city) : route('admin.cities.store') }}" class="space-y-6">
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <x-ui.card>
        <h2 class="text-lg font-semibold">Данни</h2>
        <div class="mt-5 grid gap-5 sm:grid-cols-2">
            <x-ui.input name="name" label="Име" required :value="$city->name" />
            <x-ui.input name="region" label="Област" required :value="$city->region" />
            <x-ui.input name="slug" label="URL адрес (slug)" :value="$city->slug" hint="Оставено празно се генерира от името." />
            <x-ui.input name="population" type="number" label="Население" :value="$city->population" />
            <x-ui.input name="latitude" type="number" step="0.0000001" label="Географска ширина" :value="$city->latitude"
                        hint="Използва се за изчисляване на радиуса на обслужване." />
            <x-ui.input name="longitude" type="number" step="0.0000001" label="Географска дължина" :value="$city->longitude" />
        </div>

        <div class="mt-5 space-y-3">
            <label class="flex items-center gap-3">
                <input type="checkbox" name="active" value="1" @checked(old('active', $city->active ?? true))
                       class="h-5 w-5 rounded border-ink-300 text-brand-600 focus:ring-brand-600">
                <span class="text-sm text-ink-700">Активен (може да се избира в заявките)</span>
            </label>
            <label class="flex items-start gap-3">
                <input type="checkbox" name="landing_enabled" value="1" @checked(old('landing_enabled', $city->landing_enabled ?? false))
                       class="mt-0.5 h-5 w-5 rounded border-ink-300 text-brand-600 focus:ring-brand-600">
                <span>
                    <span class="block text-sm text-ink-700">Създавай локални SEO страници</span>
                    <span class="mt-0.5 block text-sm text-ink-500">
                        Включвай само за градове с реално съдържание — не създавай стотици почти еднакви страници.
                    </span>
                </span>
            </label>
        </div>
    </x-ui.card>

    <x-ui.card>
        <h2 class="text-lg font-semibold">SEO</h2>
        <div class="mt-5 space-y-5">
            <x-ui.input name="seo_title" label="SEO заглавие" :value="$city->seo_title" />
            <x-ui.textarea name="seo_description" label="Meta description" rows="3" :value="$city->seo_description" />
            <x-ui.textarea name="seo_content" label="Локално съдържание (HTML)" rows="10" :value="$city->seo_content" />
        </div>
    </x-ui.card>

    <div class="flex items-center gap-3">
        <x-ui.button size="lg">{{ $isEdit ? 'Запази промените' : 'Добави града' }}</x-ui.button>
        <x-ui.button href="{{ route('admin.cities.index') }}" variant="ghost" size="lg">Отказ</x-ui.button>
    </div>
</form>
