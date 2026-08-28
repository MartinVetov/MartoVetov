<?php

namespace App\Http\Requests\Provider;

use App\Rules\BulgarianPhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProviderProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isProvider() ?? false;
    }

    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'min:2', 'max:180'],
            'eik' => ['nullable', 'string', 'regex:/^[0-9]{9}([0-9]{4})?$/'],
            'contact_name' => ['nullable', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:32', new BulgarianPhone],
            'email' => ['required', 'email:rfc', 'max:180'],
            'website' => ['nullable', 'url', 'max:180'],
            'description' => ['nullable', 'string', 'max:3000'],
            'city_id' => ['required', Rule::exists('cities', 'id')->where('active', true)],
            'address' => ['nullable', 'string', 'max:255'],
            'service_radius' => ['required', 'integer', 'between:5,400'],
            'working_hours' => ['nullable', 'string', 'max:120'],
            'facebook' => ['nullable', 'url', 'max:180'],
            'instagram' => ['nullable', 'url', 'max:180'],
            'logo' => [
                'nullable', 'image',
                'mimes:'.implode(',', config('nt.uploads.mimes')),
                'max:'.config('nt.uploads.max_size_kb'),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'company_name' => 'име на фирмата',
            'eik' => 'ЕИК',
            'phone' => 'телефон',
            'email' => 'имейл',
            'city_id' => 'град',
            'service_radius' => 'радиус на обслужване',
        ];
    }

    public function messages(): array
    {
        return ['eik.regex' => 'ЕИК трябва да съдържа 9 или 13 цифри.'];
    }
}
