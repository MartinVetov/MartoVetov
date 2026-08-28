<?php

namespace Tests\Feature\Admin;

use App\Models\City;
use App\Models\EquipmentCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_the_admin_can_create_a_category_with_seo_fields(): void
    {
        $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name' => 'Кранове',
            'slug' => 'kranove',
            'sort_order' => 9,
            'lead_price' => 30,
            'seo_title' => 'Кран под наем',
            'seo_description' => 'Кранове под наем в България.',
            'active' => '1',
        ])->assertRedirect(route('admin.categories.index'));

        $category = EquipmentCategory::where('slug', 'kranove')->first();

        $this->assertNotNull($category);
        $this->assertEquals(30.0, $category->leadPrice());
        $this->assertSame('Кран под наем', $category->seo_title);
    }

    public function test_a_category_slug_must_be_unique(): void
    {
        EquipmentCategory::factory()->create(['slug' => 'kranove']);

        $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name' => 'Кранове',
            'slug' => 'kranove',
            'sort_order' => 1,
        ])->assertSessionHasErrors('slug');
    }

    public function test_deactivating_a_category_hides_its_landing_page(): void
    {
        $category = EquipmentCategory::factory()->create();

        $this->get(route('categories.show', $category))->assertOk();

        $this->actingAs($this->admin)->put(route('admin.categories.update', $category), [
            'name' => $category->name,
            'slug' => $category->slug,
            'sort_order' => 0,
        ]);

        $this->get(route('categories.show', $category))->assertNotFound();
    }

    public function test_the_admin_can_create_a_city_and_enable_its_landing_page(): void
    {
        $category = EquipmentCategory::factory()->create();

        $this->actingAs($this->admin)->post(route('admin.cities.store'), [
            'name' => 'Казанлък',
            'slug' => 'kazanlak',
            'region' => 'Стара Загора',
            'active' => '1',
            'landing_enabled' => '1',
        ])->assertRedirect(route('admin.cities.index'));

        $city = City::where('slug', 'kazanlak')->first();

        $this->assertNotNull($city);
        $this->get(route('categories.city', [$category, $city]))->assertOk();
    }

    public function test_a_city_without_a_landing_page_returns_404(): void
    {
        $category = EquipmentCategory::factory()->create();
        $city = City::factory()->create();

        $this->get(route('categories.city', [$category, $city]))->assertNotFound();
    }

    public function test_only_admins_can_manage_the_catalog(): void
    {
        $provider = User::factory()->provider()->create();

        $this->actingAs($provider)->get(route('admin.categories.index'))->assertForbidden();
        $this->actingAs($provider)->get(route('admin.cities.index'))->assertForbidden();
    }
}
