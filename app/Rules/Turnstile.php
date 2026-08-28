<?php

namespace App\Rules;

use App\Services\TurnstileService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Turnstile implements ValidationRule
{
    public function __construct(protected ?string $ip = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! app(TurnstileService::class)->verify(is_string($value) ? $value : null, $this->ip)) {
            $fail('Не успяхме да потвърдим, че не си робот. Опитай отново.');
        }
    }
}
