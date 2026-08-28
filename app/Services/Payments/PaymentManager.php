<?php

namespace App\Services\Payments;

use InvalidArgumentException;

/**
 * Избира платежен канал по име. Нови канали се регистрират тук, без да се
 * променя бизнес логиката, която ги ползва.
 */
class PaymentManager
{
    /** @var array<string, class-string<PaymentGateway>> */
    protected array $gateways = [
        'manual' => ManualGateway::class,
    ];

    public function driver(?string $name = null): PaymentGateway
    {
        $name ??= config('nt.payments.default', 'manual');

        if (! isset($this->gateways[$name])) {
            throw new InvalidArgumentException("Няма регистриран платежен канал [{$name}].");
        }

        return app($this->gateways[$name]);
    }

    /** @param  class-string<PaymentGateway>  $class */
    public function extend(string $name, string $class): void
    {
        $this->gateways[$name] = $class;
    }

    /** @return array<int, string> */
    public function available(): array
    {
        return array_keys($this->gateways);
    }
}
