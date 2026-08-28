@php
    $isEdit = isset($profile) && $profile->exists;
    $cityOptions = $cities->mapWithKeys(fn ($c) => [$c->id => $c->name.' ('.$c->region.')']);
@endphp

<form method="POST"
      action="{{ $isEdit ? route('provider.profile.update') : route('provider.profile.store') }}"
      enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <x-ui.card>
        <h2 class="text-lg font-semibold">Данни за фирмата</h2>
        <div class="mt-5 grid gap-5 sm:grid-cols-2">
            <x-ui.input name="company_name" label="Име на фирмата" required :value="$isEdit ? $profile->company_name : null" />
            <x-ui.input name="eik" label="ЕИК (по желание)" :value="$isEdit ? $profile->eik : null" placeholder="9 или 13 цифри" />
            <x-ui.input name="contact_name" label="Лице за контакт" :value="$isEdit ? $profile->contact_name : null" />
            <x-ui.input name="phone" type="tel" label="Телефон" required :value="$isEdit ? $profile->phone : auth()->user()->phone" />
            <x-ui.input name="email" type="email" label="Имейл" required :value="$isEdit ? $profile->email : auth()->user()->email" />
            <x-ui.input name="website" type="url" label="Уебсайт" :value="$isEdit ? $profile->website : null" placeholder="https://" />
        </div>

        <div class="mt-5">
            <x-ui.textarea name="description" label="Описание" rows="5" :value="$isEdit ? $profile->description : null"
                           placeholder="С какво се занимавате, от колко години, какви обекти обслужвате…" />
        </div>
    </x-ui.card>

    <x-ui.card>
        <h2 class="text-lg font-semibold">Локация и обхват</h2>
        <div class="mt-5 grid gap-5 sm:grid-cols-2">
            <x-ui.select name="city_id" label="Базов град" required :options="$cityOptions"
                         placeholder="Избери град" :value="$isEdit ? $profile->city_id : null" />
            <x-ui.input name="service_radius" type="number" label="Радиус на обслужване (км)" required
                        :value="$isEdit ? $profile->service_radius : 50" min="5" max="400" />
            <x-ui.input name="address" label="Адрес" :value="$isEdit ? $profile->address : null" />
            <x-ui.input name="working_hours" label="Работно време" :value="$isEdit ? $profile->working_hours : null"
                        placeholder="напр. Пон–Съб 07:00–19:00" />
        </div>
    </x-ui.card>

    <x-ui.card>
        <h2 class="text-lg font-semibold">Лого и социални профили</h2>
        <div class="mt-5 grid gap-5 sm:grid-cols-2">
            <x-ui.input name="facebook" type="url" label="Facebook" :value="$isEdit ? $profile->facebook : null" placeholder="https://" />
            <x-ui.input name="instagram" type="url" label="Instagram" :value="$isEdit ? $profile->instagram : null" placeholder="https://" />
        </div>

        <div class="mt-5">
            <label for="logo" class="mb-1.5 block text-sm font-medium text-ink-800">Лого</label>
            @if ($isEdit && $profile->logoUrl())
                <img src="{{ $profile->logoUrl() }}" alt="Текущо лого" class="mb-3 h-16 w-16 rounded-lg object-cover ring-1 ring-ink-200">
            @endif
            <input type="file" name="logo" id="logo" accept="image/jpeg,image/png,image/webp"
                   class="block w-full rounded-lg border border-ink-300 bg-white p-2.5 text-sm text-ink-600 file:mr-3 file:rounded-md file:border-0 file:bg-ink-100 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-ink-700">
            <x-ui.error name="logo" />
        </div>
    </x-ui.card>

    <x-ui.button size="lg">{{ $isEdit ? 'Запази промените' : 'Създай профила' }}</x-ui.button>
</form>
