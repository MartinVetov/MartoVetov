<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Приема български мобилни и стационарни номера в разпространените формати:
 * 0888123456, +359888123456, 00359 888 123 456, 02/123 45 67.
 */
class BulgarianPhone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $digits = preg_replace('/[^0-9+]/', '', (string) $value);
        $normalized = preg_replace('/^(\+359|00359|359)/', '0', (string) $digits);

        if (! preg_match('/^0[2-9][0-9]{7,8}$/', (string) $normalized)) {
            $fail('Въведи валиден български телефонен номер, например 0888 123 456.');
        }
    }
}
