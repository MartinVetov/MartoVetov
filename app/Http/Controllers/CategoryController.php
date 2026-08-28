<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\EquipmentCategory;
use App\Models\ProviderProfile;
use App\Services\AnalyticsService;
use Illuminate\Contracts\View\View;

class CategoryController extends Controller
{
    public function __construct(protected AnalyticsService $analytics) {}

    public function index(): View
    {
        $categories = EquipmentCategory::query()
            ->active()
            ->roots()
            ->with('children')
            ->withCount(['equipment' => fn ($q) => $q->where('active', true)])
            ->get();

        return view('tehnika.index', compact('categories'));
    }

    public function show(EquipmentCategory $category): View
    {
        abort_unless($category->active, 404);

        $category->load('children');
        $this->analytics->record(AnalyticsService::CATEGORY_VIEW, $category);

        $providers = $this->providersFor($category);
        $cities = $this->citiesWithLandingFor($category);

        return view('tehnika.show', compact('category', 'providers', 'cities'));
    }

    public function showInCity(EquipmentCategory $category, City $city): View
    {
        abort_unless($category->active && $city->active && $city->landing_enabled, 404);

        $category->load('children');
        $this->analytics->record(AnalyticsService::CATEGORY_VIEW, $category, ['city' => $city->slug]);

        $providers = $this->providersFor($category, $city);
        $cities = $this->citiesWithLandingFor($category)->reject(fn (City $c) => $c->id === $city->id);
        $otherCategories = EquipmentCategory::active()->roots()->where('id', '!=', $category->id)->take(5)->get();

        return view('tehnika.city', compact('category', 'city', 'providers', 'cities', 'otherCategories'));
    }

    /** Доставчици с активна техника в категорията (и по избор — в даден град). */
    protected function providersFor(EquipmentCategory $category, ?City $city = null)
    {
        return ProviderProfile::query()
            ->dispatchable()
            ->with(['city', 'equipment' => fn ($q) => $q->active()->where('equipment_category_id', $category->id)])
            ->whereHas('equipment', fn ($q) => $q->active()->where('equipment_category_id', $category->id))
            ->when($city, function ($query) use ($city) {
                $query->where(function ($q) use ($city) {
                    $q->where('city_id', $city->id)
                        ->orWhereHas('serviceAreas', fn ($sa) => $sa->where('city_id', $city->id));
                });
            })
            ->orderByDesc('verified')
            ->orderByDesc('rating_avg')
            ->take(12)
            ->get();
    }

    protected function citiesWithLandingFor(EquipmentCategory $category)
    {
        return City::withLanding()->orderByDesc('population')->take(8)->get();
    }
}
