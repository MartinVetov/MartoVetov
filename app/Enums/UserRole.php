<?php

namespace App\Enums;

enum UserRole: string
{
    case Customer = 'customer';
    case Provider = 'provider';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Customer => 'Клиент',
            self::Provider => 'Доставчик',
            self::Admin => 'Администратор',
        };
    }
}
