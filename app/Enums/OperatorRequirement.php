<?php

namespace App\Enums;

enum OperatorRequirement: string
{
    case Required = 'required';
    case NotRequired = 'not_required';
    case Any = 'any';
    case Unknown = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::Required => 'Да, нужен ми е оператор',
            self::NotRequired => 'Не, имам оператор',
            self::Any => 'Няма значение',
            self::Unknown => 'Не знам',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Required => 'С оператор',
            self::NotRequired => 'Без оператор',
            self::Any => 'Няма значение',
            self::Unknown => 'Не е уточнено',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $c) => [$c->value => $c->label()])->all();
    }
}
