<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Models\EquipmentCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = EquipmentCategory::query()
            ->roots()
            ->with('children')
            ->withCount(['equipment', 'leads'])
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.create', [
            'category' => new EquipmentCategory(['active' => true, 'sort_order' => 0]),
            'parents' => EquipmentCategory::roots()->get(),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        EquipmentCategory::create($request->validated());
        $this->flushCaches();

        return redirect()->route('admin.categories.index')->with('success', 'Категорията е създадена.');
    }

    public function edit(EquipmentCategory $category): View
    {
        return view('admin.categories.edit', [
            'category' => $category,
            'parents' => EquipmentCategory::roots()->where('id', '!=', $category->id)->get(),
        ]);
    }

    public function update(StoreCategoryRequest $request, EquipmentCategory $category): RedirectResponse
    {
        $category->update($request->validated());
        $this->flushCaches();

        return redirect()->route('admin.categories.index')->with('success', 'Категорията е обновена.');
    }

    protected function flushCaches(): void
    {
        Cache::forget('home.categories');
        Cache::forget('home.stats');
    }
}
