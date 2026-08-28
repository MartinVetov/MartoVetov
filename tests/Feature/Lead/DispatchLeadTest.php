<?php

namespace Tests\Feature\Lead;

use App\Enums\LeadProviderStatus;
use App\Enums\LeadStatus;
use App\Models\City;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\Lead;
use App\Models\ProviderProfile;
use App\Notifications\LeadAssignedToProvider;
use App\Services\LeadDispatchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DispatchLeadTest extends TestCase
{
    use RefreshDatabase;

    protected function provider(City $city, EquipmentCategory $category): ProviderProfile
    {
        $provider = ProviderProfile::factory()->create(['city_id' => $city->id]);
        Equipment::factory()->for($provider)->create(['equipment_category_id' => $category->id]);

        return $provider;
    }

    public function test_sending_a_lead_creates_assignments_and_notifies_providers(): void
    {
        Notification::fake();

        $city = City::factory()->create();
        $category = EquipmentCategory::factory()->create(['lead_price' => 20]);
        $lead = Lead::factory()->create(['city_id' => $city->id, 'equipment_category_id' => $category->id]);

        $one = $this->provider($city, $category);
        $two = $this->provider($city, $category);

        $sent = app(LeadDispatchService::class)->send($lead, [$one->id, $two->id]);

        $this->assertCount(2, $sent);
        $this->assertSame(LeadStatus::Sent, $lead->fresh()->status);
        $this->assertSame(2, $lead->fresh()->sent_count);

        $assignment = $lead->assignments()->where('provider_profile_id', $one->id)->first();
        $this->assertSame(LeadProviderStatus::Sent, $assignment->status);
        $this->assertNotNull($assignment->sent_at);
        $this->assertNotNull($assignment->match_score);
        $this->assertEquals(20.0, (float) $assignment->price);

        Notification::assertSentTo([$one->user, $two->user], LeadAssignedToProvider::class);
    }

    public function test_a_lead_is_not_sent_twice_to_the_same_provider(): void
    {
        Notification::fake();

        $city = City::factory()->create();
        $category = EquipmentCategory::factory()->create();
        $lead = Lead::factory()->create(['city_id' => $city->id, 'equipment_category_id' => $category->id]);
        $provider = $this->provider($city, $category);

        $service = app(LeadDispatchService::class);
        $service->send($lead, [$provider->id]);
        $second = $service->send($lead, [$provider->id]);

        $this->assertCount(0, $second);
        $this->assertSame(1, $lead->assignments()->count());
    }

    public function test_blocked_providers_never_receive_a_lead(): void
    {
        Notification::fake();

        $city = City::factory()->create();
        $category = EquipmentCategory::factory()->create();
        $lead = Lead::factory()->create(['city_id' => $city->id, 'equipment_category_id' => $category->id]);

        $blocked = ProviderProfile::factory()->blocked()->create(['city_id' => $city->id]);
        Equipment::factory()->for($blocked)->create(['equipment_category_id' => $category->id]);

        $sent = app(LeadDispatchService::class)->send($lead, [$blocked->id]);

        $this->assertCount(0, $sent);
        Notification::assertNothingSent();
    }
}
