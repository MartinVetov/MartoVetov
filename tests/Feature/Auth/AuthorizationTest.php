<?php

namespace Tests\Feature\Auth;

use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_protected_areas(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
        $this->get('/profil')->assertRedirect(route('login'));
    }

    public function test_providers_cannot_reach_the_admin_area(): void
    {
        $provider = User::factory()->provider()->create();

        $this->actingAs($provider)->get('/admin')->assertForbidden();
        $this->actingAs($provider)->get('/admin/zaiavki')->assertForbidden();
    }

    public function test_customers_cannot_reach_the_provider_area(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->get('/profil')->assertForbidden();
    }

    public function test_admins_can_reach_the_admin_area(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin')->assertOk();
    }

    public function test_provider_without_a_profile_is_sent_to_create_one(): void
    {
        $provider = User::factory()->provider()->create();

        $this->actingAs($provider)->get('/profil')->assertRedirect(route('provider.profile.create'));
    }

    public function test_provider_with_a_profile_sees_the_dashboard(): void
    {
        $profile = ProviderProfile::factory()->create();

        $this->actingAs($profile->user)->get('/profil')->assertOk()->assertSee('Обобщение');
    }
}
