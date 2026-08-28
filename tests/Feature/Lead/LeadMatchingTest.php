<?php

namespace Tests\Feature\Lead;

use App\Enums\OperatorRequirement;
use App\Models\City;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\Lead;
use App\Models\ProviderProfile;
use App\Services\LeadMatchingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadMatchingTest extends TestCase
{
    use RefreshDatabase;

    protected LeadMatchingService $matching;

    protected City $sofia;

    protected City $plovdiv;

    protected EquipmentCategory $excavators;

    protected function setUp(): void
    {
        parent::setUp();

        $this->matching = app(LeadMatchingService::class);

        $this->sofia = City::factory()->create(['name' => 'София', 'latitude' => 42.6977, 'longitude' => 23.3219]);
        $this->plovdiv = City::factory()->create(['name' => 'Пловдив', 'latitude' => 42.1354, 'longitude' => 24.7453]);
        $this->excavators = EquipmentCategory::factory()->create(['slug' => 'mini-bager', 'name' => 'Мини багери']);
    }

    protected function provider(City $city, array $state = []): ProviderProfile
    {
        return ProviderProfile::factory()->create(array_merge(['city_id' => $city->id], $state));
    }

    protected function lead(array $overrides = []): Lead
    {
        return Lead::factory()->create(array_merge([
            'equipment_category_id' => $this->excavators->id,
            'city_id' => $this->sofia->id,
            'operator_required' => OperatorRequirement::Required,
        ], $overrides));
    }

    public function test_a_provider_in_the_same_city_with_the_right_category_scores_highest(): void
    {
        $local = $this->provider($this->sofia);
        Equipment::factory()->for($local)->create(['equipment_category_id' => $this->excavators->id]);

        $distant = $this->provider($this->plovdiv, ['service_radius' => 20]);
        Equipment::factory()->for($distant)->create(['equipment_category_id' => $this->excavators->id]);

        $candidates = $this->matching->candidates($this->lead());

        $this->assertSame($local->id, $candidates->first()->provider->id);
        $this->assertGreaterThan(
            $candidates->last()->score,
            $candidates->first()->score
        );
    }

    public function test_providers_without_matching_equipment_are_excluded(): void
    {
        $other = EquipmentCategory::factory()->create();

        $provider = $this->provider($this->sofia);
        Equipment::factory()->for($provider)->create(['equipment_category_id' => $other->id]);

        $this->assertCount(0, $this->matching->candidates($this->lead()));
    }

    public function test_inactive_equipment_does_not_qualify_a_provider(): void
    {
        $provider = $this->provider($this->sofia);
        Equipment::factory()->inactive()->for($provider)->create(['equipment_category_id' => $this->excavators->id]);

        $this->assertCount(0, $this->matching->candidates($this->lead()));
    }

    public function test_unapproved_and_blocked_providers_are_excluded(): void
    {
        foreach ([ProviderProfile::factory()->pending(), ProviderProfile::factory()->blocked()] as $factory) {
            $provider = $factory->create(['city_id' => $this->sofia->id]);
            Equipment::factory()->for($provider)->create(['equipment_category_id' => $this->excavators->id]);
        }

        $this->assertCount(0, $this->matching->candidates($this->lead()));
    }

    public function test_a_declared_service_area_makes_a_provider_from_another_city_eligible(): void
    {
        $provider = $this->provider($this->plovdiv, ['service_radius' => 10]);
        Equipment::factory()->for($provider)->create(['equipment_category_id' => $this->excavators->id]);
        $provider->serviceAreas()->create(['city_id' => $this->sofia->id, 'radius' => 10]);

        $result = $this->matching->score($this->lead(), $provider->fresh()->load('serviceAreas', 'equipment'));

        $this->assertArrayHasKey('service_area', $result->breakdown);
    }

    public function test_a_provider_within_the_radius_gets_the_distance_points(): void
    {
        // София – Пловдив е около 130 км по права линия.
        $provider = $this->provider($this->plovdiv, ['service_radius' => 200]);
        Equipment::factory()->for($provider)->create(['equipment_category_id' => $this->excavators->id]);

        $result = $this->matching->score($this->lead(), $provider->fresh()->load('serviceAreas', 'equipment'));

        $this->assertArrayHasKey('within_radius', $result->breakdown);
        $this->assertNotNull($result->distanceKm);
    }

    public function test_a_verified_provider_scores_above_an_identical_unverified_one(): void
    {
        $verified = $this->provider($this->sofia, ['verified' => true]);
        Equipment::factory()->for($verified)->create(['equipment_category_id' => $this->excavators->id]);

        $plain = $this->provider($this->sofia);
        Equipment::factory()->for($plain)->create(['equipment_category_id' => $this->excavators->id]);

        $candidates = $this->matching->candidates($this->lead());

        $this->assertSame($verified->id, $candidates->first()->provider->id);
    }

    public function test_a_lead_without_a_category_still_finds_providers_with_active_equipment(): void
    {
        $provider = $this->provider($this->sofia);
        Equipment::factory()->for($provider)->create(['equipment_category_id' => $this->excavators->id]);

        $lead = $this->lead(['equipment_category_id' => null, 'category_unknown' => true]);

        $this->assertCount(1, $this->matching->candidates($lead));
    }

    public function test_the_operator_requirement_is_scored(): void
    {
        $provider = $this->provider($this->sofia);
        Equipment::factory()->withoutOperator()->for($provider)->create(['equipment_category_id' => $this->excavators->id]);

        $result = $this->matching->score($this->lead(), $provider->fresh()->load('serviceAreas', 'equipment'));

        $this->assertArrayNotHasKey('operator_match', $result->breakdown);
    }
}
