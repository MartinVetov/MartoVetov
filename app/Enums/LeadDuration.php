<?php

namespace App\Enums;

enum LeadDuration: string
{
    case Hours = 'hours';
    case OneDay = 'one_day';
    case TwoThreeDays = 'two_three_days';
    case MoreThanThreeDays = 'more_than_three_days';
    case Unknown = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::Hours => 'Няколко часа',
            self::OneDay => '1 ден',
            self::TwoThreeDays => '2–3 дни',
            self::MoreThanThreeDays => 'Повече от 3 дни',
            self::Unknown => 'Не знам',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $c) => [$c->value => $c->label()])->all();
    }
}
