<?php

namespace App\Http\Controllers\Provider;

use App\Enums\LeadProviderStatus;
use App\Http\Controllers\Controller;
use App\Models\LeadProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $profile = $request->user()->providerProfile;
        $profile->loadCount(['equipment', 'serviceAreas']);

        $assignments = LeadProvider::where('provider_profile_id', $profile->id);

        $stats = [
            'total' => (clone $assignments)->count(),
            'new' => (clone $assignments)->where('status', LeadProviderStatus::Sent)->count(),
            'accepted' => (clone $assignments)->where('status', LeadProviderStatus::Accepted)->count(),
            'completed' => (clone $assignments)->where('status', LeadProviderStatus::Completed)->count(),
            'this_month' => (clone $assignments)->where('sent_at', '>=', now()->startOfMonth())->count(),
            'profile_views' => $profile->profile_views,
        ];

        $latest = LeadProvider::with(['lead.category', 'lead.city'])
            ->where('provider_profile_id', $profile->id)
            ->latest('sent_at')
            ->take(8)
            ->get();

        return view('provider.dashboard', compact('profile', 'stats', 'latest'));
    }
}
