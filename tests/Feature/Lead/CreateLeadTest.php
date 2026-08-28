<?php

namespace Tests\Feature\Lead;

use App\Enums\LeadStatus;
use App\Jobs\ProcessNewLead;
use App\Models\City;
use App\Models\EquipmentCategory;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CreateLeadTest extends TestCase
{
    use RefreshDatabase;

    protected City $city;

    protected EquipmentCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->city = City::factory()->create(['name' => 'София']);
        $this->category = EquipmentCategory::factory()->create(['slug' => 'mini-bager', 'name' => 'Мини багери']);
    }

    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'equipment_category_id' => $this->category->id,
            'category_unknown' => 0,
            'description' => 'Трябва ми изкоп за основи на къща около 30 метра в двора.',
            'city_id' => $this->city->id,
            'district' => 'Драгалевци',
            'requested_date' => now()->addWeek()->toDateString(),
            'duration' => 'two_three_days',
            'operator_required' => 'required',
            'contact_name' => 'Милен Христов',
            'contact_phone' => '0888456789',
            'contact_email' => 'milen@example.bg',
            'consent' => '1',
        ], $overrides);
    }

    public function test_the_lead_form_is_reachable(): void
    {
        $this->get('/zaiavka')
            ->assertOk()
            ->assertSee('Кажи какво трябва да свършиш')
            ->assertSee('Не знам каква техника ми трябва');
    }

    public function test_a_visitor_can_submit_a_lead(): void
    {
        Bus::fake();

        $response = $this->post('/zaiavka', $this->validPayload());

        $lead = Lead::first();

        $this->assertNotNull($lead);
        $response->assertRedirect(route('leads.thanks', $lead));

        $this->assertSame($this->category->id, $lead->equipment_category_id);
        $this->assertSame($this->city->id, $lead->city_id);
        $this->assertSame(LeadStatus::New, $lead->status);
        $this->assertNotNull($lead->consent_at);
        $this->assertNotNull($lead->reference);

        Bus::assertDispatched(ProcessNewLead::class);
    }

    public function test_the_thank_you_page_shows_the_reference_number(): void
    {
        Bus::fake();

        $this->post('/zaiavka', $this->validPayload());
        $lead = Lead::first();

        $this->get(route('leads.thanks', $lead))
            ->assertOk()
            ->assertSee('Готово!')
            ->assertSee($lead->reference);
    }

    public function test_a_lead_can_be_submitted_without_knowing_the_category(): void
    {
        Bus::fake();

        $this->post('/zaiavka', $this->validPayload([
            'equipment_category_id' => null,
            'category_unknown' => 1,
            'description' => 'Имам стар навес за събаряне и много отпадъци за извозване.',
        ]))->assertSessionHasNoErrors();

        $lead = Lead::first();

        $this->assertTrue($lead->category_unknown);
        $this->assertNull($lead->equipment_category_id);
    }

    public function test_a_category_is_required_when_the_visitor_did_not_choose_unknown(): void
    {
        $this->post('/zaiavka', $this->validPayload(['equipment_category_id' => null]))
            ->assertSessionHasErrors('equipment_category_id');

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_the_description_city_and_consent_are_required(): void
    {
        $this->post('/zaiavka', $this->validPayload([
            'description' => '',
            'city_id' => null,
            'consent' => null,
        ]))->assertSessionHasErrors(['description', 'city_id', 'consent']);
    }

    public function test_an_invalid_phone_number_is_rejected(): void
    {
        $this->post('/zaiavka', $this->validPayload(['contact_phone' => '123']))
            ->assertSessionHasErrors('contact_phone');
    }

    public function test_category_specific_attributes_are_stored_and_unknown_keys_dropped(): void
    {
        Bus::fake();

        $this->post('/zaiavka', $this->validPayload([
            'details' => [
                'depth' => '1-2m',
                'entrance_width' => '2,50 м',
                'izmisleno_pole' => 'трябва да отпадне',
            ],
        ]))->assertSessionHasNoErrors();

        $details = Lead::first()->details;

        $this->assertSame('1-2m', $details['depth']);
        $this->assertSame('2,50 м', $details['entrance_width']);
        $this->assertArrayNotHasKey('izmisleno_pole', $details);
    }

    public function test_a_select_attribute_with_an_unknown_value_is_dropped(): void
    {
        Bus::fake();

        $this->post('/zaiavka', $this->validPayload([
            'details' => ['depth' => 'nesushtestvuvashta-stoinost'],
        ]))->assertSessionHasNoErrors();

        $this->assertNull(Lead::first()->details);
    }

    public function test_photos_of_the_site_can_be_attached(): void
    {
        Bus::fake();
        Storage::fake('public');

        $this->post('/zaiavka', $this->validPayload([
            'images' => [UploadedFile::fake()->image('obekt.jpg', 800, 600)],
        ]))->assertSessionHasNoErrors();

        $lead = Lead::first();

        $this->assertCount(1, $lead->images);
        Storage::disk('public')->assertExists($lead->images->first()->path);
    }

    public function test_executable_uploads_are_rejected(): void
    {
        Storage::fake('public');

        $this->post('/zaiavka', $this->validPayload([
            'images' => [UploadedFile::fake()->create('zaraza.php', 20, 'application/x-php')],
        ]))->assertSessionHasErrors('images.0');

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_the_honeypot_field_blocks_bots(): void
    {
        $this->post('/zaiavka', $this->validPayload(['website' => 'https://spam.example']))
            ->assertSessionHasErrors('website');

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_a_form_submitted_too_fast_is_rejected(): void
    {
        $this->post('/zaiavka', $this->validPayload([
            'form_started_at' => now()->getTimestampMs(),
        ]))->assertSessionHasErrors('description');

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_duplicate_leads_are_not_created_twice(): void
    {
        Bus::fake();

        $this->post('/zaiavka', $this->validPayload());
        $this->post('/zaiavka', $this->validPayload())
            ->assertRedirect(route('leads.thanks', Lead::first()));

        $this->assertDatabaseCount('leads', 1);
    }
}
