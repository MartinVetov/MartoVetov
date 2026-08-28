<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\EquipmentCategory;
use App\Models\ProviderProfile;
use App\Services\AnalyticsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProviderDirectoryController extends Controller
{
    public function __construct(protected AnalyticsService $analytics) {}

    public function index(Request $request): View
    {
        $cities = City::active()->orderBy('name')->get();
        $categories = EquipmentCategory::active()->roots()->get();

        $providers = ProviderProfile::query()
            ->dispatchable()
            ->with(['city', 'equipment.category'])
            ->when($request->string('grad')->toString(), fn ($q, $slug) => $q->whereHas('city', fn ($c) => $c->where('slug', $slug)))
            ->when($request->string('tehnika')->toString(), fn ($q, $slug) => $q->whereHas(
                'equipment',
                fn ($e) => $e->active()->whereHas('category', fn ($c) => $c->where('slug', $slug))
            ))
            ->orderByDesc('verified')
            ->orderByDesc('rating_avg')
            ->paginate(12)
            ->withQueryString();

        return view('dostavchik.index', compact('providers', 'cities', 'categories'));
    }

    public function show(ProviderProfile $provider): View
    {
        $this->authorize('view', $provider);

        $provider->load([
            'city',
            'equipment' => fn ($q) => $q->active()->with(['category', 'images']),
            'serviceAreas.city',
            'reviews' => fn ($q) => $q->approved()->latest()->take(10),
        ]);

        $provider->increment('profile_views');
        $this->analytics->record(AnalyticsService::PROVIDER_VIEW, $provider);

        return view('dostavchik.show', compact('provider'));
    }
}
