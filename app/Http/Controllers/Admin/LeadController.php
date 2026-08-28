<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\EquipmentCategory;
use App\Models\Lead;
use App\Services\LeadDispatchService;
use App\Services\LeadMatchingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    public function __construct(
        protected LeadMatchingService $matching,
        protected LeadDispatchService $dispatcher,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Lead::class);

        $leads = Lead::query()
            ->with(['category', 'city'])
            ->withCount('assignments')
            ->when($request->string('status')->toString(), fn ($q, $s) => $q->where('status', $s))
            ->when($request->string('kategoriya')->toString(), fn ($q, $c) => $q->where('equipment_category_id', $c))
            ->when($request->string('grad')->toString(), fn ($q, $c) => $q->where('city_id', $c))
            ->when($request->string('tarsene')->toString(), function ($q, $term) {
                $q->where(function ($sub) use ($term) {
                    $sub->where('reference', 'like', "%{$term}%")
                        ->orWhere('contact_name', 'like', "%{$term}%")
                        ->orWhere('contact_phone', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.leads.index', [
            'leads' => $leads,
            'statuses' => LeadStatus::options(),
            'categories' => EquipmentCategory::active()->roots()->get(),
            'cities' => City::active()->orderBy('name')->get(),
        ]);
    }

    public function show(Lead $lead): View
    {
        $this->authorize('view', $lead);

        $lead->load(['category', 'city', 'images', 'assignments.providerProfile.city']);

        // Ранглистата на подходящите доставчици — администраторът избира от нея.
        $matches = $this->matching->scoreAll($lead)->take(20);
        $alreadySent = $lead->assignments->pluck('provider_profile_id')->all();

        return view('admin.leads.show', [
            'lead' => $lead,
            'matches' => $matches,
            'alreadySent' => $alreadySent,
            'statuses' => LeadStatus::options(),
        ]);
    }

    public function update(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);

        $data = $request->validate([
            'status' => ['required', Rule::enum(LeadStatus::class)],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
            'price' => ['nullable', 'numeric', 'between:0,10000'],
        ], [], ['status' => 'статус', 'admin_notes' => 'бележки', 'price' => 'цена']);

        $lead->update($data);

        return back()->with('success', 'Заявката е обновена.');
    }

    /** Ръчно изпращане на заявката към избрани доставчици (fallback за MVP). */
    public function send(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('dispatchToProviders', $lead);

        $data = $request->validate([
            'providers' => ['required', 'array', 'min:1', 'max:10'],
            'providers.*' => ['integer', 'exists:provider_profiles,id'],
        ], [
            'providers.required' => 'Избери поне един доставчик.',
        ]);

        $sent = $this->dispatcher->send($lead, $data['providers']);

        return back()->with(
            $sent->isEmpty() ? 'info' : 'success',
            $sent->isEmpty()
                ? 'Заявката вече е изпратена към избраните доставчици.'
                : 'Заявката е изпратена към '.$sent->count().' доставчика.'
        );
    }
}
