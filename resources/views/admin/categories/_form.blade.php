@php $isEdit = $category->exists; @endphp

<form method="POST" action="{{ $isEdit ? route('admin.categories.update', $category) : route('admin.categories.store') }}" class="space-y-6">
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <x-ui.card>
        <h2 class="text-lg font-semibold">Основни данни</h2>
        <div class="mt-5 grid gap-5 sm:grid-cols-2">
            <x-ui.input name="name" label="Име" required :value="$category->name" />
            <x-ui.input name="slug" label="URL адрес (slug)" :value="$category->slug" hint="Оставено празно се генерира от името." />
            <x-ui.input name="short_name" label="Кратко име" :value="$category->short_name" placeholder="напр. багер" />
            <x-ui.input name="icon" label="Икона (емоджи)" :value="$category->icon" placeholder="🚜" />
            <x-ui.select name="parent_id" label="Родителска категория" placeholder="Основна категория"
                         :options="$parents->pluck('name', 'id')" :value="$category->parent_id"
                         hint="Подкатегориите са типовете техника (напр. „2–5 тона“)." />
            <x-ui.input name="sort_order" type="number" label="Подредба" required :value="$category->sort_order ?? 0" />
            <x-ui.input name="lead_price" type="number" step="0.01" label="Цена на заявка (лв.)" :value="$category->lead_price"
                        hint="Празно = стойността по подразбиране ({{ number_format(config('nt.lead_price.default'), 2, ',', ' ') }} лв.)." />
        </div>

        <div class="mt-5">
            <x-ui.textarea name="description" label="Кратко описание" rows="3" :value="$category->description" />
        </div>

        <div class="mt-5 space-y-3">
            <label class="flex items-center gap-3">
                <input type="checkbox" name="active" value="1" @checked(old('active', $category->active ?? true))
                       class="h-5 w-5 rounded border-ink-300 text-brand-600 focus:ring-brand-600">
                <span class="text-sm text-ink-700">Активна категория</span>
            </label>
            <label class="flex items-center gap-3">
                <input type="checkbox" name="featured" value="1" @checked(old('featured', $category->featured ?? false))
                       class="h-5 w-5 rounded border-ink-300 text-brand-600 focus:ring-brand-600">
                <span class="text-sm text-ink-700">Показвай на началната страница</span>
            </label>
        </div>
    </x-ui.card>

    <x-ui.card>
        <h2 class="text-lg font-semibold">SEO</h2>
        <div class="mt-5 space-y-5">
            <x-ui.input name="seo_title" label="SEO заглавие" :value="$category->seo_title" />
            <x-ui.textarea name="seo_description" label="Meta description" rows="3" :value="$category->seo_description" />
            <x-ui.textarea name="seo_content" label="Съдържание на страницата (HTML)" rows="12" :value="$category->seo_content"
                           hint="Полезен текст за клиента: за какво служи техниката, как се избира размер, какво влияе на цената." />
        </div>
    </x-ui.card>

    <div class="flex items-center gap-3">
        <x-ui.button size="lg">{{ $isEdit ? 'Запази промените' : 'Създай категорията' }}</x-ui.button>
        <x-ui.button href="{{ route('admin.categories.index') }}" variant="ghost" size="lg">Отказ</x-ui.button>
    </div>
</form>
