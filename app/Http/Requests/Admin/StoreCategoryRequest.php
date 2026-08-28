<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;

        return [
            'parent_id' => ['nullable', 'exists:equipment_categories,id'],
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9-]+$/', Rule::unique('equipment_categories', 'slug')->ignore($categoryId)],
            'short_name' => ['nullable', 'string', 'max:60'],
            'icon' => ['nullable', 'string', 'max:32'],
            'description' => ['nullable', 'string', 'max:1000'],
            'seo_title' => ['nullable', 'string', 'max:180'],
            'seo_description' => ['nullable', 'string', 'max:400'],
            'seo_content' => ['nullable', 'string', 'max:20000'],
            'lead_price' => ['nullable', 'numeric', 'between:0,1000'],
            'sort_order' => ['required', 'integer', 'between:0,999'],
            'featured' => ['boolean'],
            'active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->input('slug') ?: $this->input('name')),
            'featured' => $this->boolean('featured'),
            'active' => $this->boolean('active'),
            'parent_id' => $this->input('parent_id') ?: null,
        ]);
    }

    public function attributes(): array
    {
        return [
            'name' => 'име',
            'slug' => 'URL адрес',
            'sort_order' => 'подредба',
            'lead_price' => 'цена на заявка',
        ];
    }

    public function messages(): array
    {
        return ['slug.regex' => 'URL адресът може да съдържа само малки латински букви, цифри и тире.'];
    }
}
