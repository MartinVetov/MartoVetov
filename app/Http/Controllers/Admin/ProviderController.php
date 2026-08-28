<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LeadProviderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\ProviderProfile;
use App\Notifications\ProviderApproved;
use App\Services\AnalyticsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    public function __construct(protected AnalyticsService $analytics) {}

    public function index(Request $request): View
    {
        $this->authorize('moderate', ProviderProfile::class);

        $providers = ProviderProfile::query()
            ->with(['city', 'user'])
            ->withCount(['equipment', 'leadAssignments as leads_count'])
            ->when($request->string('status')->toString(), function ($q, $status) {
                match ($status) {
                    'pending' => $q->whereNull('approved_at'),
                    'active' => $q->dispatchable(),
                    'blocked' => $q->whereNotNull('blocked_at'),
                    'verified' => $q->where('verified', true),
                    default => null,
                };
            })
            ->when($request->string('tarsene')->toString(), fn ($q, $t) => $q->where('company_name', 'like', "%{$t}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.providers.index', [
            'providers' => $providers,
            'cities' => City::active()->orderBy('name')->get(),
        ]);
    }

    public function show(ProviderProfile $provider): View
    {
        $this->authorize('moderate', ProviderProfile::class);

        $provider->load([
            'user', 'city', 'serviceAreas.city',
            'equipment.category', 'equipment.images',
            'reviews',
        ]);

        $assignments = $provider->leadAssignments()
            ->with('lead.category')
            ->latest('sent_at')
            ->take(20)
            ->get();

        $accepted = $provider->leadAssignments()
            ->whereIn('status', [
                LeadProviderStatus::Accepted,
                LeadProviderStatus::Contacted,
                LeadProviderStatus::Completed,
            ])->count();

        $total = $provider->leadAssignments()->count();

        return view('admin.providers.show', [
            'provider' => $provider,
            'assignments' => $assignments,
            'metrics' => [
                'leads' => $total,
                'accepted' => $accepted,
                'conversion' => $total > 0 ? round($accepted / $total * 100, 1) : 0.0,
                'revenue' => (float) $provider->payments()->where('status', PaymentStatus::Paid)->sum('amount'),
                'pending_revenue' => (float) $provider->payments()->where('status', PaymentStatus::Pending)->sum('amount'),
            ],
        ]);
    }

    public function update(Request $request, ProviderProfile $provider): RedirectResponse
    {
        $this->authorize('moderate', ProviderProfile::class);

        $data = $request->validate([
            'action' => ['required', 'in:approve,block,unblock,verify,unverify,toggle_active,verification'],
            'reason' => ['nullable', 'string', 'max:255'],
            'email_verified' => ['boolean'],
            'phone_verified' => ['boolean'],
            'company_verified' => ['boolean'],
        ]);

        match ($data['action']) {
            'approve' => $this->approve($provider),
            'block' => $provider->forceFill([
                'blocked_at' => now(),
                'blocked_reason' => $data['reason'] ?? null,
                'active' => false,
            ])->save(),
            'unblock' => $provider->forceFill(['blocked_at' => null, 'blocked_reason' => null])->save(),
            'verify' => $provider->forceFill(['verified' => true, 'verified_at' => now()])->save(),
            'unverify' => $provider->forceFill(['verified' => false, 'verified_at' => null])->save(),
            'toggle_active' => $provider->forceFill(['active' => ! $provider->active])->save(),
            'verification' => $provider->forceFill([
                'email_verified' => $request->boolean('email_verified'),
                'phone_verified' => $request->boolean('phone_verified'),
                'company_verified' => $request->boolean('company_verified'),
            ])->save(),
        };

        return back()->with('success', 'Профилът на доставчика е обновен.');
    }

    protected function approve(ProviderProfile $provider): void
    {
        if ($provider->isApproved()) {
            return;
        }

        $provider->forceFill([
            'approved_at' => now(),
            'active' => true,
            'blocked_at' => null,
        ])->save();

        $provider->user?->notify(new ProviderApproved);

        $this->analytics->record(AnalyticsService::PROVIDER_ACTIVATED, $provider);
    }
}
