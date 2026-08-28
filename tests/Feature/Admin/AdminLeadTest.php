<?php

namespace Tests\Feature\Admin;

use App\Enums\LeadStatus;
use App\Models\City;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\Lead;
use App\Models\ProviderProfile;
use App\Models\User;
use App\Notifications\LeadAssignedToProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminLeadTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_the_admin_sees_the_lead_list(): void
    {
        $lead = Lead::factory()->create();

        $this->actingAs($this->admin)
            ->get(route('admin.leads.index'))
            ->assertOk()
            ->assertSee($lead->reference);
    }

    public function test_the_lead_detail_shows_the_ranked_matching_providers(): void
    {
        $city = City::factory()->create();
        $category = EquipmentCategory::factory()->create();
        $lead = Lead::factory()->create(['city_id' => $city->id, 'equipment_category_id' => $category->id]);

        $provider = ProviderProfile::factory()->create(['city_id' => $city->id, 'company_name' => 'Строй Техник ЕООД']);
        Equipment::factory()->for($provider)->create(['equipment_category_id' => $category->id]);

        $this->actingAs($this->admin)
            ->get(route('admin.leads.show', $lead))
            ->assertOk()
            ->assertSee('Подходящи доставчици')
            ->assertSee('Строй Техник ЕООД');
    }

    public function test_the_admin_can_send_a_lead_to_selected_providers(): void
    {
        Notification::fake();

        $city = City::factory()->create();
        $category = EquipmentCategory::factory()->create();
        $lead = Lead::factory()->create(['city_id' => $city->id, 'equipment_category_id' => $category->id]);

        $provider = ProviderProfile::factory()->create(['city_id' => $city->id]);
        Equipment::factory()->for($provider)->create(['equipment_category_id' => $category->id]);

        $this->actingAs($this->admin)
            ->post(route('admin.leads.send', $lead), ['providers' => [$provider->id]])
            ->assertRedirect();

        $this->assertDatabaseHas('lead_provider', [
            'lead_id' => $lead->id,
            'provider_profile_id' => $provider->id,
            'status' => 'sent',
        ]);

        Notification::assertSentTo($provider->user, LeadAssignedToProvider::class);
    }

    public function test_sending_requires_at_least_one_provider(): void
    {
        $lead = Lead::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.leads.send', $lead), ['providers' => []])
            ->assertSessionHasErrors('providers');
    }

    public function test_the_admin_can_change_the_status_price_and_notes(): void
    {
        $lead = Lead::factory()->create();

        $this->actingAs($this->admin)->patch(route('admin.leads.update', $lead), [
            'status' => LeadStatus::Invalid->value,
            'price' => 25,
            'admin_notes' => 'Телефонът е грешен.',
        ])->assertRedirect();

        $lead->refresh();

        $this->assertSame(LeadStatus::Invalid, $lead->status);
        $this->assertEquals(25.0, (float) $lead->price);
        $this->assertSame('Телефонът е грешен.', $lead->admin_notes);
    }

    public function test_a_provider_cannot_send_leads(): void
    {
        $lead = Lead::factory()->create();
        $provider = ProviderProfile::factory()->create();

        $this->actingAs($provider->user)
            ->post(route('admin.leads.send', $lead), ['providers' => [$provider->id]])
            ->assertForbidden();
    }
}
