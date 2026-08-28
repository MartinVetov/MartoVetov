<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Models\ProviderProfile;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $reviews = Review::query()
            ->with(['providerProfile', 'lead'])
            ->when(
                $request->string('status')->toString(),
                fn ($q, $s) => $q->where('status', $s),
                fn ($q) => $q->where('status', ReviewStatus::Pending)
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.reviews.index', compact('reviews'));
    }

    public function update(Request $request, Review $review): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::enum(ReviewStatus::class)],
            'moderation_note' => ['nullable', 'string', 'max:500'],
        ], [], ['status' => 'статус']);

        $review->update($data);
        $this->refreshRating($review->providerProfile);

        return back()->with('success', 'Отзивът е обновен.');
    }

    /** Преизчислява рейтинга на доставчика само от публикуваните отзиви. */
    protected function refreshRating(ProviderProfile $provider): void
    {
        $approved = $provider->reviews()->approved();

        $provider->forceFill([
            'rating_count' => (clone $approved)->count(),
            'rating_avg' => round((float) (clone $approved)->avg('rating'), 2),
        ])->save();
    }
}
