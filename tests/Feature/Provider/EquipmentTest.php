<?php

namespace Tests\Feature\Provider;

use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\ProviderProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EquipmentTest extends TestCase
{
    use RefreshDatabase;

    protected ProviderProfile $profile;

    protected EquipmentCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->profile = ProviderProfile::factory()->create();
        $this->category = EquipmentCategory::factory()->create();
    }

    protected function payload(array $overrides = []): array
    {
        return array_merge([
            'equipment_category_id' => $this->category->id,
            'brand' => 'Caterpillar',
            'model' => '303.5',
            'year' => 2019,
            'weight' => 3.5,
            'operator_available' => '1',
            'price_from' => 70,
            'price_unit' => 'hour',
            'active' => '1',
        ], $overrides);
    }

    public function test_a_provider_can_add_equipment(): void
    {
        $this->actingAs($this->profile->user)
            ->post(route('provider.equipment.store'), $this->payload())
            ->assertRedirect(route('provider.equipment.index'));

        $this->assertDatabaseHas('equipment', [
            'provider_profile_id' => $this->profile->id,
            'brand' => 'Caterpillar',
            'model' => '303.5',
        ]);
    }

    public function test_equipment_validation_rejects_a_missing_category(): void
    {
        $this->actingAs($this->profile->user)
            ->post(route('provider.equipment.store'), $this->payload(['equipment_category_id' => null]))
            ->assertSessionHasErrors('equipment_category_id');
    }

    public function test_a_provider_can_update_their_own_equipment(): void
    {
        $equipment = Equipment::factory()->for($this->profile)->create(['brand' => 'JCB']);

        $this->actingAs($this->profile->user)
            ->put(route('provider.equipment.update', $equipment), $this->payload(['brand' => 'Volvo']))
            ->assertRedirect(route('provider.equipment.index'));

        $this->assertSame('Volvo', $equipment->fresh()->brand);
    }

    public function test_a_provider_cannot_touch_another_providers_equipment(): void
    {
        $other = ProviderProfile::factory()->create();
        $equipment = Equipment::factory()->for($other)->create();

        $this->actingAs($this->profile->user)
            ->put(route('provider.equipment.update', $equipment), $this->payload())
            ->assertForbidden();

        $this->actingAs($this->profile->user)
            ->delete(route('provider.equipment.destroy', $equipment))
            ->assertForbidden();
    }

    public function test_a_provider_can_delete_their_equipment(): void
    {
        $equipment = Equipment::factory()->for($this->profile)->create();

        $this->actingAs($this->profile->user)
            ->delete(route('provider.equipment.destroy', $equipment))
            ->assertRedirect();

        $this->assertDatabaseMissing('equipment', ['id' => $equipment->id]);
    }

    public function test_images_are_stored_on_the_public_disk(): void
    {
        Storage::fake('public');

        $this->actingAs($this->profile->user)->post(route('provider.equipment.store'), $this->payload([
            'images' => [UploadedFile::fake()->image('bager.jpg', 900, 700)],
        ]));

        $equipment = Equipment::first();

        $this->assertCount(1, $equipment->images);
        Storage::disk('public')->assertExists($equipment->images->first()->path);
    }

    public function test_the_free_plan_limits_the_number_of_machines(): void
    {
        Equipment::factory()->count(3)->for($this->profile)->create();

        $this->actingAs($this->profile->user)
            ->get(route('provider.equipment.create'))
            ->assertForbidden();
    }
}
