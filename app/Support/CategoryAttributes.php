<?php

namespace App\Support;

use App\Models\EquipmentCategory;

/**
 * Достъп до динамичните полета (стъпка 6), дефинирани в config/equipment_attributes.php.
 */
class CategoryAttributes
{
    public static function forSlug(?string $slug): array
    {
        if (! $slug) {
            return [];
        }

        return config('equipment_attributes.'.$slug, []);
    }

    public static function forCategory(?EquipmentCategory $category): array
    {
        return self::forSlug($category?->slug);
    }

    /** Схемата за всички категории — подава се на Alpine за динамичната форма. */
    public static function all(): array
    {
        return config('equipment_attributes', []);
    }

    /**
     * Изчиства подадените стойности: остават само познати ключове,
     * а при select — само позволените стойности.
     */
    public static function sanitize(?string $slug, array $input): array
    {
        $schema = self::forSlug($slug);
        $clean = [];

        foreach ($schema as $key => $field) {
            $value = $input[$key] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            if (($field['type'] ?? 'text') === 'select') {
                if (! array_key_exists($value, $field['options'] ?? [])) {
                    continue;
                }
            } else {
                $value = mb_substr(strip_tags((string) $value), 0, 255);
            }

            $clean[$key] = $value;
        }

        return $clean;
    }
}
