<?php

namespace App\Services\Payments;

use App\Models\Payment;

/**
 * Абстракция над платежните канали. MVP работи без онлайн плащания, но
 * бизнес логиката не е обвързана с конкретен доставчик — Stripe, myPOS или
 * банков превод се добавят като нови реализации на този интерфейс.
 */
interface PaymentGateway
{
    /** Машинно име на канала, записвано в payments.provider. */
    public function name(): string;

    /** Създава плащане и връща модела в състояние „чака плащане“. */
    public function charge(Payment $payment): Payment;

    /** Адрес, към който клиентът се пренасочва, ако каналът го изисква. */
    public function redirectUrl(Payment $payment): ?string;

    /** Обработва входящо известие от платежния доставчик. */
    public function handleCallback(array $payload): ?Payment;
}
