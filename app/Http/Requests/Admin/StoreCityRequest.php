<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreCityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $cityId = $this->route('city')?->id;

        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9-]+$/', Rule::unique('cities', 'slug')->ignore($cityId)],
            'region' => ['required', 'string', 'max:120'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'population' => ['nullable', 'integer', 'between:0,10000000'],
            'active' => ['boolean'],
            'landing_enabled' => ['boolean'],
            'seo_title' => ['nullable', 'string', 'max:180'],
            'seo_description' => ['nullable', 'string', 'max:400'],
            'seo_content' => ['nullable', 'string', 'max:20000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->input('slug') ?: $this->input('name')),
            'active' => $this->boolean('active'),
            'landing_enabled' => $this->boolean('landing_enabled'),
        ]);
    }

    public function attributes(): array
    {
        return ['name' => 'име', 'slug' => 'URL адрес', 'region' => 'област'];
    }
}
