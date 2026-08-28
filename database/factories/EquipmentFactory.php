<?php

namespace Database\Factories;

use App\Enums\PriceUnit;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\ProviderProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Equipment>
 */
class EquipmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'provider_profile_id' => ProviderProfile::factory(),
            'equipment_category_id' => EquipmentCategory::factory(),
            'brand' => fake()->randomElement(['Caterpillar', 'JCB', 'Kubota', 'Volvo', 'Takeuchi']),
            'model' => fake()->bothify('??-###'),
            'year' => fake()->numberBetween(2010, 2024),
            'weight' => fake()->randomFloat(1, 1, 10),
            'operator_available' => true,
            'operator_only' => false,
            'price_from' => fake()->numberBetween(50, 150),
            'price_unit' => PriceUnit::Hour,
            'active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['active' => false]);
    }

    public function withoutOperator(): static
    {
        return $this->state(fn () => ['operator_available' => false, 'operator_only' => false]);
    }
}
