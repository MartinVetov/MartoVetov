<?php

namespace App\Http\Requests\Auth;

use App\Rules\BulgarianPhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:180', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:32', new BulgarianPhone],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'terms' => ['accepted'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'име',
            'email' => 'имейл',
            'phone' => 'телефон',
            'password' => 'парола',
            'terms' => 'съгласие с условията',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Вече има профил с този имейл. Опитай да влезеш.',
            'terms.accepted' => 'Трябва да се съгласиш с Общите условия и Политиката за поверителност.',
        ];
    }
}
