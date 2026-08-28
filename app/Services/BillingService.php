<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\LeadProvider;
use App\Models\Payment;
use App\Services\Payments\PaymentManager;

/**
 * Начисляване на такса за приета заявка (pay per lead).
 * Цената идва от категорията, така че администраторът я управлява от панела.
 */
class BillingService
{
    public function __construct(protected PaymentManager $payments) {}

    public function chargeForLead(LeadProvider $assignment, ?string $gateway = null): Payment
    {
        $existing = Payment::where('lead_id', $assignment->lead_id)
            ->where('provider_profile_id', $assignment->provider_profile_id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $payment = Payment::create([
            'provider_profile_id' => $assignment->provider_profile_id,
            'lead_id' => $assignment->lead_id,
            'amount' => $assignment->price ?? $assignment->lead->leadPrice(),
            'currency' => config('nt.lead_price.currency'),
            'status' => PaymentStatus::Pending,
            'provider' => $gateway ?? config('nt.payments.default', 'manual'),
        ]);

        return $this->payments->driver($gateway)->charge($payment);
    }

    public function markPaid(Payment $payment, ?string $transactionId = null): Payment
    {
        $payment->forceFill([
            'status' => PaymentStatus::Paid,
            'paid_at' => now(),
            'transaction_id' => $transactionId ?? $payment->transaction_id,
        ])->save();

        return $payment;
    }
}
