<?php

namespace App\Http\Requests\Provider;

use App\Enums\PriceUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isProvider() ?? false;
    }

    public function rules(): array
    {
        return [
            'equipment_category_id' => ['required', Rule::exists('equipment_categories', 'id')->where('active', true)],
            'type' => ['nullable', 'string', 'max:120'],
            'brand' => ['nullable', 'string', 'max:80'],
            'model' => ['nullable', 'string', 'max:80'],
            'year' => ['nullable', 'integer', 'between:1960,'.(date('Y') + 1)],
            'weight' => ['nullable', 'numeric', 'between:0,500'],
            'description' => ['nullable', 'string', 'max:2000'],
            'operator_available' => ['boolean'],
            'operator_only' => ['boolean'],
            'price_from' => ['nullable', 'numeric', 'between:0,100000'],
            'price_unit' => ['required', Rule::enum(PriceUnit::class)],
            'min_duration' => ['nullable', 'string', 'max:60'],
            'active' => ['boolean'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => [
                'image',
                'mimes:'.implode(',', config('nt.uploads.mimes')),
                'max:'.config('nt.uploads.max_size_kb'),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'operator_available' => $this->boolean('operator_available'),
            'operator_only' => $this->boolean('operator_only'),
            'active' => $this->boolean('active'),
        ]);
    }

    public function validated($key = null, $default = null): mixed
    {
        $data = parent::validated();
        unset($data['images']);

        return $key ? data_get($data, $key, $default) : $data;
    }

    public function attributes(): array
    {
        return [
            'equipment_category_id' => 'категория',
            'type' => 'тип',
            'brand' => 'марка',
            'model' => 'модел',
            'year' => 'година',
            'weight' => 'тегло',
            'price_from' => 'цена от',
            'price_unit' => 'мерна единица',
        ];
    }
}
