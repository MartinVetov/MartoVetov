<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\ProviderProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_homepage_leads_with_the_main_message(): void
    {
        EquipmentCategory::factory()->create(['name' => 'Мини багери']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Намери техниката за твоята задача')
            ->assertSee('Намери техника')
            ->assertSee('Предлагам техника');
    }

    public function test_the_static_pages_respond(): void
    {
        foreach (['/kak-raboti', '/za-dostavchitsi', '/obshti-usloviya', '/politika-poveritelnost', '/kontakti', '/tehnika', '/dostavchitsi'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_a_category_page_carries_its_seo_tags(): void
    {
        $category = EquipmentCategory::factory()->create([
            'name' => 'Мини багери',
            'slug' => 'mini-bager',
            'seo_title' => 'Мини багер под наем',
            'seo_description' => 'Мини багери под наем в цяла България.',
        ]);

        $this->get(route('categories.show', $category))
            ->assertOk()
            ->assertSee('<title>Мини багер под наем | Намери Техник</title>', false)
            ->assertSee('Мини багери под наем в цяла България.', false)
            ->assertSee('rel="canonical"', false)
            ->assertSee('og:title', false)
            ->assertSee('BreadcrumbList', false);
    }

    public function test_a_city_landing_page_is_specific_to_the_city(): void
    {
        $category = EquipmentCategory::factory()->create(['name' => 'Мини багери', 'slug' => 'mini-bager']);
        $city = City::factory()->withLanding()->create(['name' => 'Пловдив', 'slug' => 'plovdiv']);

        $this->get(route('categories.city', [$category, $city]))
            ->assertOk()
            ->assertSee('Мини багери под наем в Пловдив')
            ->assertSee('Service', false);
    }

    public function test_a_public_provider_profile_is_visible_when_approved(): void
    {
        $provider = ProviderProfile::factory()->verified()->create(['company_name' => 'Строй Техник ЕООД']);
        Equipment::factory()->for($provider)->create();

        $this->get(route('providers.show', $provider))
            ->assertOk()
            ->assertSee('Строй Техник ЕООД')
            ->assertSee('Проверен доставчик')
            ->assertSee('LocalBusiness', false);
    }

    public function test_an_unapproved_provider_profile_is_not_public(): void
    {
        $provider = ProviderProfile::factory()->pending()->create();

        $this->get(route('providers.show', $provider))->assertForbidden();
    }

    public function test_the_sitemap_lists_categories_and_city_pages(): void
    {
        $category = EquipmentCategory::factory()->create(['slug' => 'mini-bager']);
        $city = City::factory()->withLanding()->create(['slug' => 'sofia']);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee(route('categories.show', $category), false)
            ->assertSee(route('categories.city', [$category, $city]), false);
    }

    public function test_robots_txt_blocks_the_private_areas(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /admin')
            ->assertSee('Disallow: /profil')
            ->assertSee(route('sitemap'));
    }
}
