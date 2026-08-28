<?php

namespace Tests\Feature\Provider;

use App\Models\City;
use App\Models\Equipment;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_provider_can_create_a_company_profile(): void
    {
        $user = User::factory()->provider()->create();
        $city = City::factory()->create();

        $this->actingAs($user)->post(route('provider.profile.store'), [
            'company_name' => 'Строй Техник ЕООД',
            'phone' => '0888123456',
            'email' => 'office@stroytehnik.bg',
            'city_id' => $city->id,
            'service_radius' => 60,
        ])->assertRedirect(route('provider.equipment.create'));

        $profile = $user->fresh()->providerProfile;

        $this->assertNotNull($profile);
        $this->assertSame('stroi-texnik-eood', $profile->slug);
        // Новият профил не е одобрен и не участва в подбора.
        $this->assertFalse($profile->isDispatchable());
    }

    public function test_an_invalid_eik_is_rejected(): void
    {
        $user = User::factory()->provider()->create();
        $city = City::factory()->create();

        $this->actingAs($user)->post(route('provider.profile.store'), [
            'company_name' => 'Тест ЕООД',
            'eik' => '12',
            'phone' => '0888123456',
            'email' => 'office@example.bg',
            'city_id' => $city->id,
            'service_radius' => 60,
        ])->assertSessionHasErrors('eik');
    }

    public function test_a_provider_can_update_their_profile(): void
    {
        $profile = ProviderProfile::factory()->create();

        $this->actingAs($profile->user)->put(route('provider.profile.update'), [
            'company_name' => 'Ново Име ООД',
            'phone' => '0888999888',
            'email' => 'novo@example.bg',
            'city_id' => $profile->city_id,
            'service_radius' => 120,
        ])->assertRedirect();

        $profile->refresh();

        $this->assertSame('Ново Име ООД', $profile->company_name);
        $this->assertSame(120, $profile->service_radius);
    }

    public function test_a_profile_cannot_be_submitted_for_review_without_equipment(): void
    {
        $profile = ProviderProfile::factory()->pending()->create();

        $this->actingAs($profile->user)->post(route('provider.profile.submit'))->assertRedirect();

        $this->assertNull($profile->fresh()->submitted_at);
    }

    public function test_a_profile_with_equipment_can_be_submitted_for_review(): void
    {
        $profile = ProviderProfile::factory()->pending()->create();
        Equipment::factory()->for($profile)->create();

        $this->actingAs($profile->user)->post(route('provider.profile.submit'))->assertRedirect();

        $this->assertNotNull($profile->fresh()->submitted_at);
    }

    public function test_service_areas_can_be_saved(): void
    {
        $profile = ProviderProfile::factory()->create();
        $cities = City::factory()->count(3)->create();

        $this->actingAs($profile->user)->put(route('provider.areas.update'), [
            'cities' => $cities->pluck('id')->all(),
            'service_radius' => 80,
        ])->assertRedirect();

        $this->assertSame(3, $profile->serviceAreas()->count());
        $this->assertSame(80, $profile->fresh()->service_radius);
    }
}
