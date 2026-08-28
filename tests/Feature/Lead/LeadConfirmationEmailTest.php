<?php

namespace Tests\Feature\Lead;

use App\Models\City;
use App\Models\EquipmentCategory;
use App\Models\Lead;
use App\Models\User;
use App\Notifications\LeadReceivedForCustomer;
use App\Notifications\NewLeadForAdmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LeadConfirmationEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function submitLead(array $overrides = []): void
    {
        $city = City::factory()->create(['name' => 'София']);
        $category = EquipmentCategory::factory()->create(['name' => 'Мини багери', 'slug' => 'mini-bager']);

        $this->post('/zaiavka', array_merge([
            'equipment_category_id' => $category->id,
            'category_unknown' => 0,
            'description' => 'Трябва ми изкоп за основи на къща около 30 метра.',
            'city_id' => $city->id,
            'duration' => 'one_day',
            'operator_required' => 'required',
            'contact_name' => 'Петър Иванов',
            'contact_phone' => '0888777666',
            'contact_email' => 'petar@example.bg',
            'consent' => '1',
        ], $overrides))->assertSessionHasNoErrors();
    }

    public function test_the_customer_receives_a_confirmation_with_the_reference_number(): void
    {
        Notification::fake();

        $this->submitLead();

        Notification::assertSentOnDemand(
            LeadReceivedForCustomer::class,
            function (LeadReceivedForCustomer $notification, array $channels, object $notifiable) {
                return $notifiable->routes['mail'] === 'petar@example.bg'
                    && $notification->lead->reference === Lead::first()->reference;
            }
        );
    }

    public function test_the_confirmation_contains_the_request_summary_and_no_promise_of_offers(): void
    {
        $lead = Lead::factory()->create([
            'contact_name' => 'Петър Иванов',
            'contact_email' => 'petar@example.bg',
        ]);

        $mail = (new LeadReceivedForCustomer($lead))->toMail($lead);
        $rendered = (string) $mail->render();

        $this->assertStringContainsString($lead->reference, $rendered);
        $this->assertStringContainsString('Здравей, Петър Иванов!', $rendered);
        $this->assertStringContainsString('Какво следва', $rendered);

        // Не обещаваме конкретен брой оферти.
        $this->assertStringNotContainsString('оферти от', $rendered);
        $this->assertDoesNotMatchRegularExpression('/\d+\s*(оферт|доставчик)/u', $rendered);
    }

    public function test_the_admin_is_notified_alongside_the_customer(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();

        $this->submitLead();

        Notification::assertSentTo($admin, NewLeadForAdmin::class);
        Notification::assertSentOnDemand(LeadReceivedForCustomer::class);
    }

    public function test_a_duplicate_submission_does_not_send_a_second_confirmation(): void
    {
        Notification::fake();

        $city = City::factory()->create();
        $category = EquipmentCategory::factory()->create();

        $payload = [
            'equipment_category_id' => $category->id,
            'category_unknown' => 0,
            'description' => 'Трябва ми изкоп за основи на къща около 30 метра.',
            'city_id' => $city->id,
            'duration' => 'one_day',
            'operator_required' => 'required',
            'contact_name' => 'Петър Иванов',
            'contact_phone' => '0888777666',
            'contact_email' => 'petar@example.bg',
            'consent' => '1',
        ];

        $this->post('/zaiavka', $payload);
        $this->post('/zaiavka', $payload);

        $this->assertSame(1, Lead::count());
        Notification::assertSentOnDemandTimes(LeadReceivedForCustomer::class, 1);
    }
}
