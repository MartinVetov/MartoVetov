<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCityRequest;
use App\Models\City;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CityController extends Controller
{
    public function index(Request $request): View
    {
        $cities = City::query()
            ->withCount(['providers', 'leads'])
            ->when($request->string('tarsene')->toString(), fn ($q, $t) => $q->where('name', 'like', "%{$t}%"))
            ->orderBy('region')
            ->orderBy('name')
            ->paginate(40)
            ->withQueryString();

        return view('admin.cities.index', compact('cities'));
    }

    public function create(): View
    {
        return view('admin.cities.create', ['city' => new City(['active' => true])]);
    }

    public function store(StoreCityRequest $request): RedirectResponse
    {
        City::create($request->validated());
        Cache::forget('home.cities');

        return redirect()->route('admin.cities.index')->with('success', 'Градът е добавен.');
    }

    public function edit(City $city): View
    {
        return view('admin.cities.edit', compact('city'));
    }

    public function update(StoreCityRequest $request, City $city): RedirectResponse
    {
        $city->update($request->validated());
        Cache::forget('home.cities');

        return redirect()->route('admin.cities.index')->with('success', 'Градът е обновен.');
    }
}
