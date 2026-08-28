<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Models\City;
use App\Models\EquipmentCategory;
use App\Models\Lead;
use App\Services\AnalyticsService;
use App\Services\LeadIntakeService;
use App\Services\TurnstileService;
use App\Support\CategoryAttributes;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Многостъпковата форма за заявка — сърцето на платформата.
 */
class LeadController extends Controller
{
    public function __construct(
        protected LeadIntakeService $intake,
        protected AnalyticsService $analytics,
    ) {}

    public function create(Request $request, TurnstileService $turnstile): View
    {
        $categories = EquipmentCategory::query()
            ->active()
            ->roots()
            ->with('children')
            ->get();

        $cities = City::active()->orderBy('name')->get(['id', 'name', 'region']);

        $this->analytics->record(AnalyticsService::LEAD_FORM_STARTED, null, [
            'category' => $request->string('kategoriya')->toString() ?: null,
        ]);

        return view('lead.create', [
            'categories' => $categories,
            'cities' => $cities,
            'attributeSchema' => CategoryAttributes::all(),
            'preselectedCategory' => $request->string('kategoriya')->toString(),
            'preselectedCity' => $request->string('grad')->toString(),
            'turnstileSiteKey' => $turnstile->enabled() ? $turnstile->siteKey() : null,
        ]);
    }

    public function store(StoreLeadRequest $request): RedirectResponse
    {
        $data = $request->leadData();

        // Не създаваме дубликат, ако същият клиент е пратил същата заявка преди малко.
        if ($duplicate = $this->intake->findRecentDuplicate($data)) {
            return redirect()
                ->route('leads.thanks', $duplicate)
                ->with('info', 'Вече получихме твоята заявка. Работим по нея.');
        }

        $lead = $this->intake->create($data, $request->file('images', []) ?? [], $request);

        return redirect()->route('leads.thanks', $lead);
    }

    public function thanks(Lead $lead): View
    {
        $lead->load('category', 'city');

        return view('lead.thanks', compact('lead'));
    }
}
