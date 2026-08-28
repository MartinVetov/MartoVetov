<?php

namespace Database\Factories;

use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<City>
 */
class CityFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->city();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'region' => $name,
            'latitude' => fake()->latitude(41.2, 44.2),
            'longitude' => fake()->longitude(22.4, 28.6),
            'population' => fake()->numberBetween(5000, 300000),
            'active' => true,
            'landing_enabled' => false,
        ];
    }

    public function withLanding(): static
    {
        return $this->state(fn () => ['landing_enabled' => true]);
    }
}
