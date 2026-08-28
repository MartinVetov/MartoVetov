<?php

namespace App\Enums;

enum LeadStatus: string
{
    case New = 'new';
    case Verified = 'verified';
    case Sent = 'sent';
    case Opened = 'opened';
    case Accepted = 'accepted';
    case Contacted = 'contacted';
    case Completed = 'completed';
    case Rejected = 'rejected';
    case Invalid = 'invalid';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Нов',
            self::Verified => 'Проверен',
            self::Sent => 'Изпратен',
            self::Opened => 'Отворен',
            self::Accepted => 'Приет',
            self::Contacted => 'Свързан',
            self::Completed => 'Завършен',
            self::Rejected => 'Отказан',
            self::Invalid => 'Невалиден',
        };
    }

    /** Tailwind класове за визуализация на статуса. */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::New => 'bg-amber-50 text-amber-800 ring-amber-200',
            self::Verified => 'bg-sky-50 text-sky-800 ring-sky-200',
            self::Sent, self::Opened => 'bg-indigo-50 text-indigo-800 ring-indigo-200',
            self::Accepted, self::Contacted => 'bg-emerald-50 text-emerald-800 ring-emerald-200',
            self::Completed => 'bg-emerald-600/10 text-emerald-900 ring-emerald-300',
            self::Rejected, self::Invalid => 'bg-rose-50 text-rose-800 ring-rose-200',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $c) => [$c->value => $c->label()])->all();
    }
}
