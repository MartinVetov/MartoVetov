<?php

namespace App\Http\Controllers;

use App\Enums\LeadProviderStatus;
use App\Enums\ReviewStatus;
use App\Http\Requests\StoreReviewRequest;
use App\Models\LeadProvider;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Отзив след завършена работа. Достъпът е с подписан линк от имейла,
 * за да не се налага клиентът да си прави профил.
 */
class ReviewController extends Controller
{
    public function create(LeadProvider $assignment): View
    {
        $this->ensureReviewable($assignment);

        $assignment->load('lead', 'providerProfile');

        return view('reviews.create', compact('assignment'));
    }

    public function store(StoreReviewRequest $request, LeadProvider $assignment): RedirectResponse
    {
        $this->ensureReviewable($assignment);

        Review::create([
            'customer_id' => $assignment->lead->customer_id,
            'provider_profile_id' => $assignment->provider_profile_id,
            'lead_id' => $assignment->lead_id,
            'author_name' => $request->validated('author_name'),
            'rating' => $request->validated('rating'),
            'comment' => $request->validated('comment'),
            'status' => ReviewStatus::Pending,
        ]);

        return redirect()
            ->route('providers.show', $assignment->providerProfile)
            ->with('success', 'Благодарим за отзива! Ще го публикуваме след преглед.');
    }

    protected function ensureReviewable(LeadProvider $assignment): void
    {
        abort_unless($assignment->status === LeadProviderStatus::Completed, 403, 'Заявката още не е приключена.');

        $exists = Review::where('lead_id', $assignment->lead_id)
            ->where('provider_profile_id', $assignment->provider_profile_id)
            ->exists();

        abort_if($exists, 403, 'За тази заявка вече има оставен отзив.');
    }
}
