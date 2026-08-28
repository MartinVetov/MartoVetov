<?php

namespace App\Enums;

enum PriceUnit: string
{
    case Hour = 'hour';
    case Day = 'day';
    case Shift = 'shift';
    case Project = 'project';

    public function label(): string
    {
        return match ($this) {
            self::Hour => 'на час',
            self::Day => 'на ден',
            self::Shift => 'на смяна',
            self::Project => 'за обект',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $c) => [$c->value => $c->label()])->all();
    }
}
