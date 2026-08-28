<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\LeadImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GdprTest extends TestCase
{
    use RefreshDatabase;

    public function test_old_leads_are_anonymized_but_statistics_are_kept(): void
    {
        Storage::fake('public');

        $lead = Lead::factory()->create([
            'contact_name' => 'Милен Христов',
            'contact_phone' => '0888456789',
            'contact_email' => 'milen@example.bg',
            'address' => 'ул. Витоша 1',
            'ip_address' => '212.5.1.1',
            'created_at' => now()->subYears(3),
        ]);

        Storage::disk('public')->put('leads/1/snimka.jpg', 'test');
        LeadImage::create(['lead_id' => $lead->id, 'path' => 'leads/1/snimka.jpg']);

        $this->artisan('nt:anonymize-leads')->assertSuccessful();

        $lead->refresh();

        $this->assertSame('Заличено', $lead->contact_name);
        $this->assertSame('', $lead->contact_phone);
        $this->assertNull($lead->address);
        $this->assertNull($lead->ip_address);
        $this->assertNotNull($lead->anonymized_at);

        // Данните за анализ остават.
        $this->assertNotNull($lead->city_id);
        $this->assertNotNull($lead->equipment_category_id);

        Storage::disk('public')->assertMissing('leads/1/snimka.jpg');
        $this->assertSame(0, $lead->images()->count());
    }

    public function test_recent_leads_are_left_untouched(): void
    {
        $lead = Lead::factory()->create(['contact_name' => 'Милен Христов']);

        $this->artisan('nt:anonymize-leads')->assertSuccessful();

        $this->assertSame('Милен Христов', $lead->fresh()->contact_name);
        $this->assertNull($lead->fresh()->anonymized_at);
    }

    public function test_personal_data_can_be_exported_for_a_data_subject_request(): void
    {
        $lead = Lead::factory()->create(['contact_email' => 'milen@example.bg']);

        $this->artisan('nt:export-personal-data', ['identifier' => 'milen@example.bg'])
            ->assertSuccessful();

        $files = glob(storage_path('app/gdpr-*.json'));

        $this->assertNotEmpty($files);

        $payload = json_decode(file_get_contents($files[0]), true);

        $this->assertSame('milen@example.bg', $payload['identifier']);
        $this->assertSame($lead->reference, $payload['zayavki'][0]['nomer']);

        foreach ($files as $file) {
            @unlink($file);
        }
    }
}
