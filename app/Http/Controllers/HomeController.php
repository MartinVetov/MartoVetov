<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\EquipmentCategory;
use App\Models\ProviderProfile;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $categories = Cache::remember('home.categories', now()->addMinutes(30), fn () => EquipmentCategory::query()
            ->active()
            ->roots()
            ->withCount(['equipment' => fn ($q) => $q->where('active', true)])
            ->get());

        $stats = Cache::remember('home.stats', now()->addMinutes(15), fn () => [
            'providers' => ProviderProfile::dispatchable()->count(),
            'cities' => City::active()->count(),
            'categories' => EquipmentCategory::active()->roots()->count(),
        ]);

        $cities = Cache::remember('home.cities', now()->addHours(6), fn () => City::withLanding()->orderByDesc('population')->take(8)->get());

        return view('home', compact('categories', 'stats', 'cities'));
    }
}
