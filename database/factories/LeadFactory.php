<?php

namespace Database\Factories;

use App\Enums\LeadDuration;
use App\Enums\LeadStatus;
use App\Enums\OperatorRequirement;
use App\Models\City;
use App\Models\EquipmentCategory;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'equipment_category_id' => EquipmentCategory::factory(),
            'category_unknown' => false,
            'description' => 'Трябва ми изкоп за основи около 30 метра в двора.',
            'city_id' => City::factory(),
            'requested_date' => now()->addDays(5)->toDateString(),
            'duration' => LeadDuration::OneDay,
            'operator_required' => OperatorRequirement::Required,
            'contact_name' => fake()->name(),
            'contact_phone' => '088'.fake()->numerify('#######'),
            'contact_email' => fake()->unique()->safeEmail(),
            'status' => LeadStatus::New,
            'consent_at' => now(),
        ];
    }

    public function withoutCategory(): static
    {
        return $this->state(fn () => ['equipment_category_id' => null, 'category_unknown' => true]);
    }
}
