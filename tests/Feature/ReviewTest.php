<?php

namespace Tests\Feature;

use App\Enums\LeadProviderStatus;
use App\Enums\ReviewStatus;
use App\Models\Lead;
use App\Models\LeadProvider;
use App\Models\ProviderProfile;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function assignment(LeadProviderStatus $status = LeadProviderStatus::Completed): LeadProvider
    {
        return LeadProvider::create([
            'lead_id' => Lead::factory()->create()->id,
            'provider_profile_id' => ProviderProfile::factory()->create()->id,
            'status' => $status,
            'sent_at' => now(),
        ]);
    }

    protected function signedUrl(string $name, LeadProvider $assignment): string
    {
        return URL::temporarySignedRoute($name, now()->addDays(30), ['assignment' => $assignment->id]);
    }

    public function test_the_review_form_needs_a_signed_link(): void
    {
        $assignment = $this->assignment();

        $this->get(route('reviews.create', $assignment))->assertForbidden();
        $this->get($this->signedUrl('reviews.create', $assignment))->assertOk();
    }

    public function test_a_review_can_only_be_left_after_the_job_is_completed(): void
    {
        $assignment = $this->assignment(LeadProviderStatus::Accepted);

        $this->get($this->signedUrl('reviews.create', $assignment))->assertForbidden();
    }

    public function test_a_submitted_review_waits_for_moderation(): void
    {
        $assignment = $this->assignment();

        $this->post($this->signedUrl('reviews.store', $assignment), [
            'author_name' => 'Милен Христов',
            'rating' => 5,
            'comment' => 'Дойдоха навреме и свършиха работата отлично.',
        ])->assertRedirect(route('providers.show', $assignment->providerProfile));

        $review = Review::first();

        $this->assertSame(ReviewStatus::Pending, $review->status);
        $this->assertSame(5, $review->rating);

        // Непубликуваните отзиви не се показват в профила.
        $this->get(route('providers.show', $assignment->providerProfile))
            ->assertDontSee('Дойдоха навреме и свършиха работата отлично.');
    }

    public function test_a_second_review_for_the_same_job_is_rejected(): void
    {
        $assignment = $this->assignment();

        $payload = ['author_name' => 'Милен', 'rating' => 5, 'comment' => 'Отлично.'];

        $this->post($this->signedUrl('reviews.store', $assignment), $payload);
        $this->post($this->signedUrl('reviews.store', $assignment), $payload)->assertForbidden();

        $this->assertSame(1, Review::count());
    }

    public function test_approving_a_review_recalculates_the_provider_rating(): void
    {
        $admin = User::factory()->admin()->create();
        $assignment = $this->assignment();

        $this->post($this->signedUrl('reviews.store', $assignment), [
            'author_name' => 'Милен',
            'rating' => 4,
            'comment' => 'Добра работа.',
        ]);

        $review = Review::first();

        $this->actingAs($admin)->patch(route('admin.reviews.update', $review), [
            'status' => ReviewStatus::Approved->value,
        ])->assertRedirect();

        $provider = $assignment->providerProfile->fresh();

        $this->assertSame(1, $provider->rating_count);
        $this->assertEquals(4.0, $provider->rating_avg);

        $this->get(route('providers.show', $provider))->assertSee('Добра работа.');
    }
}
