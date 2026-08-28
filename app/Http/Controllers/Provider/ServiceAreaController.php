<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceAreaController extends Controller
{
    public function edit(Request $request): View
    {
        $profile = $request->user()->providerProfile;
        $profile->load('serviceAreas');

        return view('provider.areas.edit', [
            'profile' => $profile,
            'cities' => City::active()->orderBy('region')->orderBy('name')->get(),
            'selected' => $profile->serviceAreas->pluck('city_id')->all(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $profile = $request->user()->providerProfile;
        $this->authorize('update', $profile);

        $data = $request->validate([
            'cities' => ['nullable', 'array', 'max:60'],
            'cities.*' => [Rule::exists('cities', 'id')->where('active', true)],
            'service_radius' => ['required', 'integer', 'between:5,400'],
        ], [], ['service_radius' => 'радиус на обслужване']);

        $profile->update(['service_radius' => $data['service_radius']]);

        $cityIds = collect($data['cities'] ?? [])->unique();

        $profile->serviceAreas()->whereNotIn('city_id', $cityIds)->delete();

        foreach ($cityIds as $cityId) {
            $profile->serviceAreas()->updateOrCreate(
                ['city_id' => $cityId],
                ['radius' => $data['service_radius']]
            );
        }

        return back()->with('success', 'Обслужваните райони са запазени.');
    }
}
