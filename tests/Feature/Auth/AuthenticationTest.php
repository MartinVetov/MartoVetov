<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_a_provider_account(): void
    {
        $response = $this->post('/registraciya', [
            'name' => 'Иван Петров',
            'email' => 'ivan@example.bg',
            'phone' => '0888123456',
            'password' => 'parola1234',
            'password_confirmation' => 'parola1234',
            'terms' => '1',
        ]);

        $response->assertRedirect(route('provider.profile.create'));

        $user = User::where('email', 'ivan@example.bg')->first();

        $this->assertNotNull($user);
        $this->assertSame(UserRole::Provider, $user->role);
        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_requires_accepting_the_terms(): void
    {
        $this->post('/registraciya', [
            'name' => 'Иван Петров',
            'email' => 'ivan@example.bg',
            'phone' => '0888123456',
            'password' => 'parola1234',
            'password_confirmation' => 'parola1234',
        ])->assertSessionHasErrors('terms');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_rejects_an_invalid_bulgarian_phone(): void
    {
        $this->post('/registraciya', [
            'name' => 'Иван Петров',
            'email' => 'ivan@example.bg',
            'phone' => '12345',
            'password' => 'parola1234',
            'password_confirmation' => 'parola1234',
            'terms' => '1',
        ])->assertSessionHasErrors('phone');
    }

    public function test_users_can_log_in_and_are_sent_to_their_area(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'parola1234']);

        $this->post('/vhod', ['email' => $admin->email, 'password' => 'parola1234'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create(['password' => 'parola1234']);

        $this->post('/vhod', ['email' => $user->email, 'password' => 'greshna-parola'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_users_can_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/izhod')->assertRedirect(route('home'));

        $this->assertGuest();
    }
}
