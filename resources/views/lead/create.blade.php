@php
    use App\Enums\LeadDuration;
    use App\Enums\OperatorRequirement;

    // При грешка от сървъра отваряме стъпката, в която е първият проблем.
    $stepOfField = [
        'equipment_category_id' => 1, 'equipment_type' => 1,
        'description' => 2, 'images' => 2, 'images.0' => 2,
        'city_id' => 3, 'district' => 3, 'address' => 3,
        'requested_date' => 4, 'requested_time' => 4, 'duration' => 4,
        'operator_required' => 5,
        'contact_name' => 7, 'contact_phone' => 7, 'contact_email' => 7,
        'consent' => 7, 'cf-turnstile-response' => 7, 'website' => 7,
    ];

    $initialStep = 1;
    foreach ($stepOfField as $field => $step) {
        if ($errors->has($field)) { $initialStep = $step; break; }
    }

    $categoryOptions = $categories->map(fn ($c) => [
        'id' => $c->id,
        'slug' => $c->slug,
        'name' => $c->name,
        'icon' => $c->icon ?: '🚜',
        'types' => $c->children->pluck('name')->all(),
    ])->values();

    $citiesByRegion = $cities->groupBy('region');
@endphp

<x-layouts.app
    title="Заяви техника"
    description="Опиши задачата си за няколко минути и ще насочим заявката към подходящи доставчици на техника в твоя район."
    :noindex="true"
    :breadcrumbs="[
        ['label' => 'Начало', 'url' => route('home')],
        ['label' => 'Заявка за техника'],
    ]"
>
    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8"
         x-data="leadForm({
            initialStep: {{ $initialStep }},
            categories: {{ Js::from($categoryOptions) }},
            preselectedSlug: @js($preselectedCategory),
            categoryUnknown: {{ old('category_unknown') ? 'true' : 'false' }},
            selectedCategoryId: {{ (int) old('equipment_category_id', 0) }},
         })"
         x-init="init()">

        <header class="mb-8">
            <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Кажи какво трябва да свършиш</h1>
            <p class="mt-2 text-ink-500">Отнема около 2 минути. Ние ще намерим подходящите доставчици на техника.</p>
        </header>

        {{-- Прогрес --}}
        <div class="mb-8">
            <div class="flex items-center justify-between text-sm font-medium text-ink-500">
                <span x-text="`Стъпка ${step} от ${totalSteps}`"></span>
                <span x-text="`${Math.round(step / totalSteps * 100)}%`"></span>
            </div>
            <div class="mt-2 h-2 overflow-hidden rounded-full bg-ink-200">
                <div class="h-full rounded-full bg-brand-600 transition-all duration-300"
                     :style="`width: ${step / totalSteps * 100}%`"></div>
            </div>
        </div>

        @if ($errors->any())
            <x-ui.alert type="error" class="mb-6">
                <p class="font-medium">Заявката не беше изпратена. Провери следното:</p>
                <ul class="mt-1 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        <form method="POST" action="{{ route('leads.store') }}" enctype="multipart/form-data"
              @submit="submitting = true">
            @csrf
            <input type="hidden" name="form_started_at" :value="startedAt">

            {{-- Скрито поле-примамка за ботове --}}
            <div class="hidden" aria-hidden="true">
                <label>Не попълвай това поле<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>

            {{-- Стъпка 1 --}}
            <section x-show="step === 1" x-cloak>
                <x-ui.card>
                    <h2 class="text-xl font-semibold">Какво ти трябва?</h2>
                    <p class="mt-1 text-sm text-ink-500">Избери вид техника или кажи, че не си сигурен.</p>

                    <input type="hidden" name="equipment_category_id" :value="categoryUnknown ? '' : (selectedCategoryId || '')">
                    <input type="hidden" name="category_unknown" :value="categoryUnknown ? 1 : 0">

                    <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3">
                        <template x-for="category in categories" :key="category.id">
                            <button type="button" @click="selectCategory(category.id)"
                                    class="flex flex-col items-start gap-2 rounded-xl border p-4 text-left transition"
                                    :class="(!categoryUnknown && selectedCategoryId === category.id)
                                        ? 'border-brand-600 bg-brand-50 ring-1 ring-brand-600'
                                        : 'border-ink-200 bg-white hover:border-brand-300'">
                                <span class="text-2xl" x-text="category.icon"></span>
                                <span class="text-sm font-semibold text-ink-900" x-text="category.name"></span>
                            </button>
                        </template>
                    </div>

                    <button type="button" @click="chooseUnknown()"
                            class="mt-4 flex w-full items-start gap-3 rounded-xl border p-4 text-left transition"
                            :class="categoryUnknown ? 'border-brand-600 bg-brand-50 ring-1 ring-brand-600' : 'border-dashed border-ink-300 hover:border-brand-300'">
                        <span class="text-2xl" aria-hidden="true">🤔</span>
                        <span>
                            <span class="block text-sm font-semibold text-ink-900">Не знам каква техника ми трябва</span>
                            <span class="mt-0.5 block text-sm text-ink-500">Опиши задачата и доставчиците ще преценят.</span>
                        </span>
                    </button>

                    {{-- Тип техника в рамките на категорията --}}
                    <div x-show="!categoryUnknown && currentTypes.length" x-cloak class="mt-6">
                        <p class="mb-2 text-sm font-medium text-ink-800">Знаеш ли какъв размер ти трябва?</p>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" @click="equipmentType = ''"
                                    class="rounded-full border px-4 py-2 text-sm font-medium transition"
                                    :class="equipmentType === '' ? 'border-brand-600 bg-brand-50 text-brand-800' : 'border-ink-200 text-ink-600 hover:border-brand-300'">
                                Не съм сигурен
                            </button>
                            <template x-for="type in currentTypes" :key="type">
                                <button type="button" @click="equipmentType = type"
                                        class="rounded-full border px-4 py-2 text-sm font-medium transition"
                                        :class="equipmentType === type ? 'border-brand-600 bg-brand-50 text-brand-800' : 'border-ink-200 text-ink-600 hover:border-brand-300'"
                                        x-text="type"></button>
                            </template>
                        </div>
                        <input type="hidden" name="equipment_type" :value="equipmentType">
                    </div>

                    <p x-show="stepError" x-cloak class="mt-4 text-sm font-medium text-rose-600" x-text="stepError"></p>
                </x-ui.card>
            </section>

            {{-- Стъпка 2 --}}
            <section x-show="step === 2" x-cloak>
                <x-ui.card>
                    <h2 class="text-xl font-semibold">Какво трябва да бъде свършено?</h2>
                    <p class="mt-1 text-sm text-ink-500">Опиши задачата с прости думи. Колкото по-ясно, толкова по-точна оферта.</p>

                    <div class="mt-5">
                        <x-ui.textarea
                            name="description"
                            label="Опиши задачата си"
                            rows="6"
                            required
                            placeholder="Трябва ми изкоп за основи на къща около 30 метра. Дворът е с тесен вход."
                            x-ref="description" />
                    </div>

                    <div class="mt-5">
                        <label for="images" class="mb-1.5 block text-sm font-medium text-ink-800">Снимки на обекта (по желание)</label>
                        <input type="file" name="images[]" id="images" multiple accept="image/jpeg,image/png,image/webp"
                               class="block w-full rounded-lg border border-ink-300 bg-white p-2.5 text-sm text-ink-600 file:mr-3 file:rounded-md file:border-0 file:bg-ink-100 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-ink-700">
                        <p class="mt-1.5 text-sm text-ink-500">
                            До {{ config('nt.uploads.max_lead_images') }} снимки, всяка до {{ round(config('nt.uploads.max_size_kb') / 1024) }} MB. Снимките помагат на доставчика да прецени достъпа.
                        </p>
                        <x-ui.error name="images.0" />
                    </div>

                    <p x-show="stepError" x-cloak class="mt-4 text-sm font-medium text-rose-600" x-text="stepError"></p>
                </x-ui.card>
            </section>

            {{-- Стъпка 3 --}}
            <section x-show="step === 3" x-cloak>
                <x-ui.card>
                    <h2 class="text-xl font-semibold">Къде ще се извърши работата?</h2>
                    <p class="mt-1 text-sm text-ink-500">Насочваме заявката към доставчици, които обслужват твоя район.</p>

                    <div class="mt-5 space-y-5">
                        <div class="w-full">
                            <label for="city_id" class="mb-1.5 block text-sm font-medium text-ink-800">
                                Населено място <span class="text-brand-600" aria-hidden="true">*</span>
                            </label>
                            <select name="city_id" id="city_id" required x-ref="city"
                                    class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-base text-ink-900 shadow-sm ring-1 ring-inset ring-ink-300 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm">
                                <option value="">Избери населено място</option>
                                @foreach ($citiesByRegion as $region => $regionCities)
                                    <optgroup label="Област {{ $region }}">
                                        @foreach ($regionCities as $city)
                                            @php
                                                $isSelected = old('city_id')
                                                    ? (string) old('city_id') === (string) $city->id
                                                    : ($preselectedCity && $city->slug === $preselectedCity);
                                            @endphp
                                            <option value="{{ $city->id }}" @selected($isSelected)>{{ $city->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            <x-ui.error name="city_id" />
                        </div>

                        <x-ui.input name="district" label="Квартал / район" placeholder="напр. Драгалевци" />
                        <x-ui.input name="address" label="Адрес (по желание)" hint="Точният адрес се вижда само от доставчиците, към които изпратим заявката." />
                    </div>

                    <p x-show="stepError" x-cloak class="mt-4 text-sm font-medium text-rose-600" x-text="stepError"></p>
                </x-ui.card>
            </section>

            {{-- Стъпка 4 --}}
            <section x-show="step === 4" x-cloak>
                <x-ui.card>
                    <h2 class="text-xl font-semibold">Кога ти трябва техниката?</h2>

                    <div class="mt-5 grid gap-5 sm:grid-cols-2">
                        <x-ui.input type="date" name="requested_date" label="Дата" :value="old('requested_date')" min="{{ now()->toDateString() }}" />
                        <x-ui.input name="requested_time" label="Приблизителен час" placeholder="напр. сутринта, 09:00" />
                    </div>

                    <label class="mt-4 flex items-start gap-3">
                        <input type="checkbox" name="date_flexible" value="1" @checked(old('date_flexible'))
                               class="mt-0.5 h-5 w-5 rounded border-ink-300 text-brand-600 focus:ring-brand-600">
                        <span class="text-sm text-ink-700">Датата е гъвкава — мога да се съобразя с доставчика</span>
                    </label>

                    <fieldset class="mt-6">
                        <legend class="text-sm font-medium text-ink-800">Колко време ще отнеме работата?</legend>
                        <div class="mt-3 grid gap-2 sm:grid-cols-2">
                            @foreach (LeadDuration::options() as $value => $label)
                                <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-ink-200 px-4 py-3 transition hover:border-brand-300 has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50">
                                    <input type="radio" name="duration" value="{{ $value }}" required
                                           @checked(old('duration', 'unknown') === $value)
                                           class="h-4 w-4 border-ink-300 text-brand-600 focus:ring-brand-600">
                                    <span class="text-sm font-medium text-ink-800">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        <x-ui.error name="duration" />
                    </fieldset>
                </x-ui.card>
            </section>

            {{-- Стъпка 5 --}}
            <section x-show="step === 5" x-cloak>
                <x-ui.card>
                    <h2 class="text-xl font-semibold">Нужен ли ти е оператор?</h2>
                    <p class="mt-1 text-sm text-ink-500">Повечето доставчици предлагат техника заедно с оператор.</p>

                    <fieldset class="mt-5">
                        <legend class="sr-only">Оператор</legend>
                        <div class="space-y-2">
                            @foreach (OperatorRequirement::options() as $value => $label)
                                <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-ink-200 px-4 py-3 transition hover:border-brand-300 has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50">
                                    <input type="radio" name="operator_required" value="{{ $value }}" required
                                           @checked(old('operator_required', 'required') === $value)
                                           class="h-4 w-4 border-ink-300 text-brand-600 focus:ring-brand-600">
                                    <span class="text-sm font-medium text-ink-800">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        <x-ui.error name="operator_required" />
                    </fieldset>
                </x-ui.card>
            </section>

            {{-- Стъпка 6 — динамични полета според категорията --}}
            <section x-show="step === 6" x-cloak>
                <x-ui.card>
                    <h2 class="text-xl font-semibold">Още няколко детайла</h2>
                    <p class="mt-1 text-sm text-ink-500">Помагат на доставчика да избере правилната машина. Може да пропуснеш, ако не знаеш.</p>

                    <div x-show="!hasAttributes" x-cloak class="mt-5 rounded-lg border border-dashed border-ink-300 bg-ink-50 p-5 text-sm text-ink-600">
                        За избраната техника няма допълнителни въпроси. Продължи към контактите.
                    </div>

                    @foreach ($attributeSchema as $slug => $fields)
                        <div x-show="categorySlug === @js($slug)" x-cloak class="mt-5 space-y-5">
                            @foreach ($fields as $key => $field)
                                @php $inputName = "details[{$key}]"; $inputId = "attr-{$slug}-{$key}"; @endphp

                                @if (($field['type'] ?? 'text') === 'select')
                                    <div>
                                        <label for="{{ $inputId }}" class="mb-1.5 block text-sm font-medium text-ink-800">{{ $field['label'] }}</label>
                                        <select name="{{ $inputName }}" id="{{ $inputId }}"
                                                :disabled="categorySlug !== @js($slug)"
                                                class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-base text-ink-900 shadow-sm ring-1 ring-inset ring-ink-300 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm">
                                            <option value="">Не е уточнено</option>
                                            @foreach ($field['options'] as $optValue => $optLabel)
                                                <option value="{{ $optValue }}" @selected(old("details.{$key}") === (string) $optValue)>{{ $optLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @else
                                    <div>
                                        <label for="{{ $inputId }}" class="mb-1.5 block text-sm font-medium text-ink-800">{{ $field['label'] }}</label>
                                        <input type="text" name="{{ $inputName }}" id="{{ $inputId }}"
                                               value="{{ old("details.{$key}") }}"
                                               placeholder="{{ $field['placeholder'] ?? '' }}"
                                               :disabled="categorySlug !== @js($slug)"
                                               class="block w-full rounded-lg border-0 px-3.5 py-2.5 text-base text-ink-900 shadow-sm ring-1 ring-inset ring-ink-300 placeholder:text-ink-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm">
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endforeach
                </x-ui.card>
            </section>

            {{-- Стъпка 7 --}}
            <section x-show="step === 7" x-cloak>
                <x-ui.card>
                    <h2 class="text-xl font-semibold">Как да се свържем с теб?</h2>
                    <p class="mt-1 text-sm text-ink-500">Данните ти се виждат само от доставчиците, към които насочим заявката.</p>

                    <div class="mt-5 space-y-5">
                        <x-ui.input name="contact_name" label="Име" required autocomplete="name" x-ref="contactName" />
                        <x-ui.input name="contact_phone" label="Телефон" type="tel" required autocomplete="tel"
                                    placeholder="0888 123 456" x-ref="contactPhone" />
                        <x-ui.input name="contact_email" label="Имейл" type="email" required autocomplete="email"
                                    hint="Изпращаме потвърждение и номер на заявката." />
                    </div>

                    <label class="mt-6 flex items-start gap-3">
                        <input type="checkbox" name="consent" value="1" required @checked(old('consent'))
                               class="mt-0.5 h-5 w-5 rounded border-ink-300 text-brand-600 focus:ring-brand-600">
                        <span class="text-sm text-ink-700">
                            Съгласявам се с <a href="{{ route('terms') }}" target="_blank" class="font-medium text-brand-700 underline">Общите условия</a>
                            и <a href="{{ route('privacy') }}" target="_blank" class="font-medium text-brand-700 underline">Политиката за поверителност</a>.
                        </span>
                    </label>
                    <x-ui.error name="consent" />

                    @if ($turnstileSiteKey)
                        <div class="mt-5">
                            <div class="cf-turnstile" data-sitekey="{{ $turnstileSiteKey }}" data-language="bg"></div>
                            <x-ui.error name="cf-turnstile-response" />
                        </div>
                        @push('head')
                            <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
                        @endpush
                    @endif
                </x-ui.card>
            </section>

            {{-- Навигация --}}
            <div class="mt-6 flex items-center justify-between gap-3">
                <x-ui.button type="button" variant="outline" x-show="step > 1" x-cloak @click="prev()">Назад</x-ui.button>
                <span x-show="step === 1"></span>

                <x-ui.button type="button" x-show="step < totalSteps" @click="next()" size="lg" class="ml-auto">
                    Продължи
                </x-ui.button>

                <x-ui.button x-show="step === totalSteps" x-cloak size="lg" class="ml-auto" ::disabled="submitting">
                    <span x-show="!submitting">Изпрати заявката</span>
                    <span x-show="submitting" x-cloak>Изпращаме…</span>
                </x-ui.button>
            </div>
        </form>
    </div>

    @push('head')
        <script>
            function leadForm(config) {
                return {
                    step: config.initialStep,
                    totalSteps: 7,
                    categories: config.categories,
                    selectedCategoryId: config.selectedCategoryId || null,
                    categoryUnknown: config.categoryUnknown,
                    equipmentType: @js(old('equipment_type', '')),
                    startedAt: Date.now(),
                    submitting: false,
                    stepError: '',

                    init() {
                        if (config.preselectedSlug) {
                            const match = this.categories.find((c) => c.slug === config.preselectedSlug);
                            if (match) this.selectedCategoryId = match.id;
                        }

                        window.ntTrack?.('lead_form_started');

                        // Отчитаме изоставена форма, ако потребителят напусне без изпращане.
                        window.addEventListener('beforeunload', () => {
                            if (!this.submitting) {
                                window.ntTrack?.('lead_form_abandoned', { step: String(this.step) });
                            }
                        });
                    },

                    get current() {
                        return this.categories.find((c) => c.id === this.selectedCategoryId) || null;
                    },

                    get categorySlug() {
                        return this.categoryUnknown ? null : (this.current?.slug ?? null);
                    },

                    get currentTypes() {
                        return this.current?.types ?? [];
                    },

                    get hasAttributes() {
                        return Boolean(this.categorySlug && @js(array_keys(config('equipment_attributes', [])))
                            .includes(this.categorySlug));
                    },

                    selectCategory(id) {
                        this.selectedCategoryId = id;
                        this.categoryUnknown = false;
                        this.equipmentType = '';
                        this.stepError = '';
                    },

                    chooseUnknown() {
                        this.categoryUnknown = true;
                        this.selectedCategoryId = null;
                        this.equipmentType = '';
                        this.stepError = '';
                    },

                    validateStep() {
                        this.stepError = '';

                        if (this.step === 1 && !this.categoryUnknown && !this.selectedCategoryId) {
                            this.stepError = 'Избери каква техника ти трябва или отбележи, че не знаеш.';
                            return false;
                        }

                        if (this.step === 2) {
                            const value = this.$refs.description?.value.trim() ?? '';
                            if (value.length < 15) {
                                this.stepError = 'Опиши задачата с поне 15 символа.';
                                return false;
                            }
                        }

                        if (this.step === 3 && !this.$refs.city?.value) {
                            this.stepError = 'Избери населено място.';
                            return false;
                        }

                        return true;
                    },

                    next() {
                        if (!this.validateStep()) return;

                        // Стъпка 6 се пропуска, ако за категорията няма допълнителни въпроси.
                        let target = this.step + 1;
                        if (target === 6 && !this.hasAttributes) target = 7;

                        this.step = Math.min(target, this.totalSteps);
                        window.ntTrack?.('lead_form_step', { step: String(this.step) });
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    },

                    prev() {
                        let target = this.step - 1;
                        if (target === 6 && !this.hasAttributes) target = 5;

                        this.step = Math.max(target, 1);
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    },
                };
            }
        </script>
    @endpush
</x-layouts.app>
