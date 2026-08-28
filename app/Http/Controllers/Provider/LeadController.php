<?php

namespace App\Http\Controllers\Provider;

use App\Enums\LeadProviderStatus;
use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Models\LeadProvider;
use App\Notifications\RequestReviewFromCustomer;
use App\Services\AnalyticsService;
use App\Services\BillingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    public function __construct(
        protected AnalyticsService $analytics,
        protected BillingService $billing,
    ) {}

    public function index(Request $request): View
    {
        $profile = $request->user()->providerProfile;

        $assignments = LeadProvider::query()
            ->with(['lead.category', 'lead.city'])
            ->where('provider_profile_id', $profile->id)
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->latest('sent_at')
            ->paginate(15)
            ->withQueryString();

        return view('provider.leads.index', compact('assignments', 'profile'));
    }

    public function show(Request $request, LeadProvider $assignment): View
    {
        $this->authorizeAssignment($request, $assignment);

        $assignment->load(['lead.category', 'lead.city', 'lead.images']);

        // Първото отваряне се отчита като преглед.
        if ($assignment->viewed_at === null) {
            $assignment->forceFill([
                'viewed_at' => now(),
                'status' => $assignment->status === LeadProviderStatus::Sent
                    ? LeadProviderStatus::Viewed
                    : $assignment->status,
            ])->save();

            if ($assignment->lead->status === LeadStatus::Sent) {
                $assignment->lead->forceFill(['status' => LeadStatus::Opened])->save();
            }

            $this->analytics->record(AnalyticsService::LEAD_VIEWED, $assignment->lead, [
                'provider_id' => $assignment->provider_profile_id,
            ]);
        }

        return view('provider.leads.show', compact('assignment'));
    }

    public function update(Request $request, LeadProvider $assignment): RedirectResponse
    {
        $this->authorizeAssignment($request, $assignment);

        $data = $request->validate([
            'action' => ['required', Rule::in(['accept', 'decline', 'contacted', 'complete'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        match ($data['action']) {
            'accept' => $this->accept($assignment),
            'decline' => $this->decline($assignment, $data['notes'] ?? null),
            'contacted' => $this->markContacted($assignment),
            'complete' => $this->complete($assignment),
        };

        return back()->with('success', 'Статусът на заявката е обновен.');
    }

    protected function accept(LeadProvider $assignment): void
    {
        $assignment->forceFill([
            'status' => LeadProviderStatus::Accepted,
            'accepted_at' => now(),
        ])->save();

        $assignment->lead->forceFill(['status' => LeadStatus::Accepted])->save();

        // Приетата заявка се таксува (pay per lead) — плащането се отчита отделно.
        $this->billing->chargeForLead($assignment);

        $this->analytics->record(AnalyticsService::LEAD_ACCEPTED, $assignment->lead, [
            'provider_id' => $assignment->provider_profile_id,
        ]);
    }

    protected function decline(LeadProvider $assignment, ?string $notes): void
    {
        $assignment->forceFill([
            'status' => LeadProviderStatus::Declined,
            'declined_at' => now(),
            'notes' => $notes,
        ])->save();
    }

    protected function markContacted(LeadProvider $assignment): void
    {
        $assignment->forceFill([
            'status' => LeadProviderStatus::Contacted,
            'contacted_at' => now(),
        ])->save();

        $assignment->lead->forceFill(['status' => LeadStatus::Contacted])->save();
    }

    protected function complete(LeadProvider $assignment): void
    {
        $assignment->forceFill([
            'status' => LeadProviderStatus::Completed,
            'completed_at' => now(),
        ])->save();

        $assignment->lead->forceFill(['status' => LeadStatus::Completed])->save();

        $this->analytics->record(AnalyticsService::JOB_COMPLETED, $assignment->lead, [
            'provider_id' => $assignment->provider_profile_id,
        ]);

        Notification::route('mail', $assignment->lead->contact_email)
            ->notify(new RequestReviewFromCustomer($assignment));
    }

    protected function authorizeAssignment(Request $request, LeadProvider $assignment): void
    {
        abort_unless(
            $assignment->provider_profile_id === $request->user()->providerProfile?->id,
            403,
            'Тази заявка не е изпратена към твоя профил.'
        );
    }
}
