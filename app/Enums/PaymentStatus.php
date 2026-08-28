<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Чака плащане',
            self::Paid => 'Платено',
            self::Failed => 'Неуспешно',
            self::Refunded => 'Възстановено',
        };
    }
}
