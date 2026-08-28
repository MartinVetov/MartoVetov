<?php

namespace Tests\Feature\Admin;

use App\Models\ProviderProfile;
use App\Models\User;
use App\Notifications\ProviderApproved;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminProviderTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_the_admin_can_approve_a_provider_and_the_provider_is_notified(): void
    {
        Notification::fake();

        $provider = ProviderProfile::factory()->pending()->create();

        $this->actingAs($this->admin)
            ->patch(route('admin.providers.update', $provider), ['action' => 'approve'])
            ->assertRedirect();

        $provider->refresh();

        $this->assertTrue($provider->isApproved());
        $this->assertTrue($provider->active);
        $this->assertTrue($provider->isDispatchable());

        Notification::assertSentTo($provider->user, ProviderApproved::class);
    }

    public function test_the_admin_can_block_and_unblock_a_provider(): void
    {
        $provider = ProviderProfile::factory()->create();

        $this->actingAs($this->admin)->patch(route('admin.providers.update', $provider), [
            'action' => 'block',
            'reason' => 'Оплаквания от клиенти.',
        ]);

        $provider->refresh();
        $this->assertTrue($provider->isBlocked());
        $this->assertFalse($provider->isDispatchable());

        $this->actingAs($this->admin)->patch(route('admin.providers.update', $provider), ['action' => 'unblock']);

        $this->assertFalse($provider->fresh()->isBlocked());
    }

    public function test_only_the_admin_can_mark_a_provider_as_verified(): void
    {
        $provider = ProviderProfile::factory()->create();

        $this->actingAs($provider->user)
            ->patch(route('admin.providers.update', $provider), ['action' => 'verify'])
            ->assertForbidden();

        $this->assertFalse($provider->fresh()->verified);

        $this->actingAs($this->admin)
            ->patch(route('admin.providers.update', $provider), ['action' => 'verify']);

        $this->assertTrue($provider->fresh()->verified);
    }

    public function test_the_admin_can_set_the_individual_verification_flags(): void
    {
        $provider = ProviderProfile::factory()->create();

        $this->actingAs($this->admin)->patch(route('admin.providers.update', $provider), [
            'action' => 'verification',
            'email_verified' => '1',
            'company_verified' => '1',
        ]);

        $provider->refresh();

        $this->assertTrue($provider->email_verified);
        $this->assertTrue($provider->company_verified);
        $this->assertFalse($provider->phone_verified);
    }

    public function test_the_provider_list_and_detail_are_reachable(): void
    {
        $provider = ProviderProfile::factory()->create();

        $this->actingAs($this->admin)->get(route('admin.providers.index'))->assertOk()->assertSee($provider->company_name);
        $this->actingAs($this->admin)->get(route('admin.providers.show', $provider))->assertOk();
    }
}
