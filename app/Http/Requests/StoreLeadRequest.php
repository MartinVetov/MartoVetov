<?php

namespace App\Http\Requests;

use App\Enums\LeadDuration;
use App\Enums\OperatorRequirement;
use App\Models\EquipmentCategory;
use App\Rules\BulgarianPhone;
use App\Rules\Turnstile;
use App\Support\CategoryAttributes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $uploads = config('nt.uploads');

        return [
            // Стъпка 1 — техника
            'equipment_category_id' => [
                'nullable',
                Rule::requiredIf(fn () => ! $this->boolean('category_unknown')),
                Rule::exists('equipment_categories', 'id')->where('active', true),
            ],
            'category_unknown' => ['boolean'],
            'equipment_type' => ['nullable', 'string', 'max:120'],

            // Стъпка 2 — задача
            'description' => ['required', 'string', 'min:15', 'max:3000'],
            'images' => ['nullable', 'array', 'max:'.$uploads['max_lead_images']],
            'images.*' => [
                'image',
                'mimes:'.implode(',', $uploads['mimes']),
                'max:'.$uploads['max_size_kb'],
                'dimensions:max_width=10000,max_height=10000',
            ],

            // Стъпка 3 — локация
            'city_id' => ['required', Rule::exists('cities', 'id')->where('active', true)],
            'district' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],

            // Стъпка 4 — кога
            'requested_date' => ['nullable', 'date', 'after_or_equal:today'],
            'requested_time' => ['nullable', 'string', 'max:32'],
            'date_flexible' => ['boolean'],
            'duration' => ['required', Rule::enum(LeadDuration::class)],

            // Стъпка 5 — оператор
            'operator_required' => ['required', Rule::enum(OperatorRequirement::class)],

            // Стъпка 6 — допълнителни характеристики
            'details' => ['nullable', 'array'],
            'details.*' => ['nullable', 'string', 'max:255'],

            // Стъпка 7 — контакти и защита
            'contact_name' => ['required', 'string', 'min:2', 'max:120'],
            'contact_phone' => ['required', 'string', 'max:32', new BulgarianPhone],
            'contact_email' => ['required', 'email:rfc', 'max:180'],
            'consent' => ['accepted'],
            'cf-turnstile-response' => ['nullable', new Turnstile($this->ip())],

            // Скрити полета срещу ботове.
            'website' => ['nullable', 'size:0'],
            'form_started_at' => ['nullable', 'integer'],
        ];
    }

    public function attributes(): array
    {
        return [
            'equipment_category_id' => 'категория техника',
            'description' => 'описание на задачата',
            'city_id' => 'населено място',
            'duration' => 'продължителност',
            'operator_required' => 'нужда от оператор',
            'contact_name' => 'име',
            'contact_phone' => 'телефон',
            'contact_email' => 'имейл',
            'consent' => 'съгласие с условията',
        ];
    }

    public function messages(): array
    {
        return [
            'equipment_category_id.required' => 'Избери каква техника ти трябва или отбележи, че не знаеш.',
            'description.required' => 'Опиши какво трябва да бъде свършено.',
            'description.min' => 'Опиши задачата с поне 15 символа, за да могат доставчиците да преценят.',
            'city_id.required' => 'Избери населеното място, където ще се извърши работата.',
            'consent.accepted' => 'Трябва да се съгласиш с Общите условия и Политиката за поверителност.',
            'website.size' => 'Заявката изглежда изпратена автоматично.',
            'images.*.image' => 'Прикачените файлове трябва да бъдат изображения.',
            'images.*.max' => 'Всяко изображение трябва да е под :max KB.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'category_unknown' => $this->boolean('category_unknown'),
            'date_flexible' => $this->boolean('date_flexible'),
            'contact_phone' => $this->normalizePhone((string) $this->input('contact_phone')),
        ]);

        if ($this->boolean('category_unknown')) {
            $this->merge(['equipment_category_id' => null, 'equipment_type' => null]);
        }
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $this->validateHoneypotTiming($validator);
                $this->validateEquipmentType($validator);
            },
        ];
    }

    /** Форма, попълнена за по-малко от няколко секунди, е почти сигурно бот. */
    protected function validateHoneypotTiming(Validator $validator): void
    {
        $startedAt = (int) $this->input('form_started_at');
        $minimum = (int) config('nt.antispam.min_form_seconds');

        if ($startedAt > 0 && (now()->timestamp - intdiv($startedAt, 1000)) < $minimum) {
            $validator->errors()->add('description', 'Заявката беше изпратена твърде бързо. Опитай отново.');
        }
    }

    /** Типът техника трябва да принадлежи на избраната категория. */
    protected function validateEquipmentType(Validator $validator): void
    {
        $type = $this->input('equipment_type');
        $categoryId = $this->input('equipment_category_id');

        if (! $type || ! $categoryId) {
            return;
        }

        $exists = EquipmentCategory::where('parent_id', $categoryId)->where('name', $type)->exists();

        if (! $exists) {
            $validator->errors()->add('equipment_type', 'Избраният тип техника не е валиден за тази категория.');
        }
    }

    protected function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/[^0-9+]/', '', $phone);

        return (string) preg_replace('/^(\+359|00359|359)/', '0', (string) $digits);
    }

    /** Данните за създаване на заявката, включително slug-а на категорията. */
    public function leadData(): array
    {
        $data = $this->safe()->except(['images', 'consent', 'cf-turnstile-response', 'website', 'form_started_at']);

        $category = $this->input('equipment_category_id')
            ? EquipmentCategory::find($this->input('equipment_category_id'))
            : null;

        $data['category_slug'] = $category?->slug;
        $data['details'] = CategoryAttributes::sanitize($category?->slug, $this->input('details', []) ?? []);
        $data['price'] = $category?->leadPrice();

        return $data;
    }
}
