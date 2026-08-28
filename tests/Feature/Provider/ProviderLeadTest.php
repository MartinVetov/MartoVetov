<?php

namespace Tests\Feature\Provider;

use App\Enums\LeadProviderStatus;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\LeadProvider;
use App\Models\Payment;
use App\Models\ProviderProfile;
use App\Notifications\RequestReviewFromCustomer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ProviderLeadTest extends TestCase
{
    use RefreshDatabase;

    protected ProviderProfile $profile;

    protected LeadProvider $assignment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->profile = ProviderProfile::factory()->create();

        $lead = Lead::factory()->create([
            'status' => LeadStatus::Sent,
            'contact_phone' => '0888112233',
        ]);

        $this->assignment = LeadProvider::create([
            'lead_id' => $lead->id,
            'provider_profile_id' => $this->profile->id,
            'status' => LeadProviderStatus::Sent,
            'sent_at' => now(),
            'price' => 15,
        ]);
    }

    public function test_a_provider_sees_their_leads(): void
    {
        $this->actingAs($this->profile->user)
            ->get(route('provider.leads.index'))
            ->assertOk()
            ->assertSee('Моите заявки');
    }

    public function test_opening_a_lead_marks_it_as_viewed(): void
    {
        $this->actingAs($this->profile->user)
            ->get(route('provider.leads.show', $this->assignment))
            ->assertOk();

        $assignment = $this->assignment->fresh();

        $this->assertNotNull($assignment->viewed_at);
        $this->assertSame(LeadProviderStatus::Viewed, $assignment->status);
        $this->assertSame(LeadStatus::Opened, $assignment->lead->status);
    }

    public function test_contact_details_are_hidden_until_the_lead_is_accepted(): void
    {
        $this->actingAs($this->profile->user)
            ->get(route('provider.leads.show', $this->assignment))
            ->assertOk()
            ->assertDontSee($this->assignment->lead->contact_phone)
            ->assertSee('след като приемеш заявката');
    }

    public function test_accepting_a_lead_reveals_the_contact_details_and_creates_a_charge(): void
    {
        $this->actingAs($this->profile->user)
            ->patch(route('provider.leads.update', $this->assignment), ['action' => 'accept'])
            ->assertRedirect();

        $assignment = $this->assignment->fresh();

        $this->assertSame(LeadProviderStatus::Accepted, $assignment->status);
        $this->assertNotNull($assignment->accepted_at);
        $this->assertSame(LeadStatus::Accepted, $assignment->lead->status);

        $this->assertDatabaseHas('payments', [
            'provider_profile_id' => $this->profile->id,
            'lead_id' => $assignment->lead_id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->profile->user)
            ->get(route('provider.leads.show', $assignment))
            ->assertSee($assignment->lead->contact_phone);
    }

    public function test_a_lead_is_charged_only_once(): void
    {
        $this->actingAs($this->profile->user)
            ->patch(route('provider.leads.update', $this->assignment), ['action' => 'accept']);
        $this->actingAs($this->profile->user)
            ->patch(route('provider.leads.update', $this->assignment), ['action' => 'accept']);

        $this->assertSame(1, Payment::count());
    }

    public function test_a_provider_can_decline_a_lead(): void
    {
        $this->actingAs($this->profile->user)
            ->patch(route('provider.leads.update', $this->assignment), [
                'action' => 'decline',
                'notes' => 'Няма свободна машина за тази дата.',
            ]);

        $assignment = $this->assignment->fresh();

        $this->assertSame(LeadProviderStatus::Declined, $assignment->status);
        $this->assertNotNull($assignment->declined_at);
    }

    public function test_completing_a_job_asks_the_customer_for_a_review(): void
    {
        Notification::fake();

        $this->assignment->forceFill(['status' => LeadProviderStatus::Contacted])->save();

        $this->actingAs($this->profile->user)
            ->patch(route('provider.leads.update', $this->assignment), ['action' => 'complete']);

        $assignment = $this->assignment->fresh();

        $this->assertSame(LeadProviderStatus::Completed, $assignment->status);
        $this->assertSame(LeadStatus::Completed, $assignment->lead->status);

        Notification::assertSentOnDemand(RequestReviewFromCustomer::class);
    }

    public function test_a_provider_cannot_open_a_lead_sent_to_someone_else(): void
    {
        $other = ProviderProfile::factory()->create();

        $this->actingAs($other->user)
            ->get(route('provider.leads.show', $this->assignment))
            ->assertForbidden();

        $this->actingAs($other->user)
            ->patch(route('provider.leads.update', $this->assignment), ['action' => 'accept'])
            ->assertForbidden();
    }
}
