<?php

namespace App\Enums;

enum ReviewStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Чака одобрение',
            self::Approved => 'Публикуван',
            self::Rejected => 'Отхвърлен',
        };
    }
}
