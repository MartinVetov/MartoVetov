<?php

namespace App\Services\Payments;

use App\Enums\PaymentStatus;
use App\Models\Payment;

/**
 * Канал по подразбиране за MVP: фактуриране и банков превод извън системата.
 * Администраторът маркира плащането като платено ръчно.
 */
class ManualGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'manual';
    }

    public function charge(Payment $payment): Payment
    {
        $payment->forceFill([
            'provider' => $this->name(),
            'status' => PaymentStatus::Pending,
        ])->save();

        return $payment;
    }

    public function redirectUrl(Payment $payment): ?string
    {
        return null;
    }

    public function handleCallback(array $payload): ?Payment
    {
        return null;
    }
}
