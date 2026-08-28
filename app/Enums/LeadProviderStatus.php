<?php

namespace App\Enums;

enum LeadProviderStatus: string
{
    case Sent = 'sent';
    case Viewed = 'viewed';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case Contacted = 'contacted';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Sent => 'Изпратена',
            self::Viewed => 'Прегледана',
            self::Accepted => 'Приета',
            self::Declined => 'Отказана',
            self::Contacted => 'Свързан с клиента',
            self::Completed => 'Завършена',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Sent => 'bg-amber-50 text-amber-800 ring-amber-200',
            self::Viewed => 'bg-sky-50 text-sky-800 ring-sky-200',
            self::Accepted, self::Contacted => 'bg-emerald-50 text-emerald-800 ring-emerald-200',
            self::Completed => 'bg-emerald-600/10 text-emerald-900 ring-emerald-300',
            self::Declined => 'bg-rose-50 text-rose-800 ring-rose-200',
        };
    }
}
