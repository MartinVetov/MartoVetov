<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProviderProfile>
 */
class ProviderProfileFactory extends Factory
{
    public function definition(): array
    {
        $company = fake()->company();

        return [
            'user_id' => User::factory()->provider(),
            'company_name' => $company,
            'slug' => Str::slug($company).'-'.fake()->unique()->numberBetween(1, 99999),
            'contact_name' => fake()->name(),
            'phone' => '088'.fake()->numerify('#######'),
            'email' => fake()->unique()->safeEmail(),
            'description' => fake()->paragraph(),
            'city_id' => City::factory(),
            'service_radius' => 50,
            'active' => true,
            'approved_at' => now(),
            'submitted_at' => now(),
        ];
    }

    /** Профил, който още не е одобрен от администратор. */
    public function pending(): static
    {
        return $this->state(fn () => ['approved_at' => null, 'submitted_at' => null, 'active' => false]);
    }

    public function verified(): static
    {
        return $this->state(fn () => ['verified' => true, 'verified_at' => now()]);
    }

    public function blocked(): static
    {
        return $this->state(fn () => ['blocked_at' => now(), 'active' => false]);
    }
}
