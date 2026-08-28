<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Provider\StoreEquipmentRequest;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentImage;
use App\Services\ImageUploadService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    public function __construct(protected ImageUploadService $images) {}

    public function index(Request $request): View
    {
        $profile = $request->user()->providerProfile;

        $equipment = $profile->equipment()
            ->with(['category', 'images'])
            ->latest()
            ->get();

        return view('provider.equipment.index', compact('profile', 'equipment'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $this->authorize('create', Equipment::class);

        return view('provider.equipment.create', [
            'categories' => $this->categories(),
            'profile' => $request->user()->providerProfile,
        ]);
    }

    public function store(StoreEquipmentRequest $request): RedirectResponse
    {
        $this->authorize('create', Equipment::class);

        $profile = $request->user()->providerProfile;

        $equipment = $profile->equipment()->create($request->validated());
        $this->storeImages($equipment, $request);

        return redirect()
            ->route('provider.equipment.index')
            ->with('success', 'Техниката е добавена.');
    }

    public function edit(Equipment $equipment): View
    {
        $this->authorize('update', $equipment);

        $equipment->load('images');

        return view('provider.equipment.edit', [
            'equipment' => $equipment,
            'categories' => $this->categories(),
        ]);
    }

    public function update(StoreEquipmentRequest $request, Equipment $equipment): RedirectResponse
    {
        $this->authorize('update', $equipment);

        $equipment->update($request->validated());
        $this->storeImages($equipment, $request);

        return redirect()
            ->route('provider.equipment.index')
            ->with('success', 'Промените са запазени.');
    }

    public function destroy(Equipment $equipment): RedirectResponse
    {
        $this->authorize('delete', $equipment);

        foreach ($equipment->images as $image) {
            $this->images->delete($image->path);
        }

        $equipment->delete();

        return back()->with('success', 'Техниката е премахната.');
    }

    public function destroyImage(EquipmentImage $image): RedirectResponse
    {
        $this->authorize('update', $image->equipment);

        $this->images->delete($image->path);
        $image->delete();

        return back()->with('success', 'Снимката е изтрита.');
    }

    protected function storeImages(Equipment $equipment, StoreEquipmentRequest $request): void
    {
        $files = $request->file('images', []) ?? [];

        if (! $files) {
            return;
        }

        $limit = $equipment->providerProfile->planSetting('max_images_per_equipment') ?? 10;
        $existing = $equipment->images()->count();
        $sort = $existing;

        foreach ($files as $file) {
            if ($existing >= $limit) {
                break;
            }

            EquipmentImage::create([
                'equipment_id' => $equipment->id,
                'path' => $this->images->store($file, 'equipment/'.$equipment->id),
                'sort_order' => $sort++,
            ]);

            $existing++;
        }
    }

    protected function categories()
    {
        return EquipmentCategory::active()->roots()->with('children')->get();
    }
}
