<?php

namespace App\Http\Controllers;

use App\Models\EquipmentCategory;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public function howItWorks(): View
    {
        return view('pages.how-it-works');
    }

    public function forProviders(): View
    {
        $categories = EquipmentCategory::active()->roots()->get();

        return view('pages.for-providers', compact('categories'));
    }

    public function terms(): View
    {
        return view('pages.terms');
    }

    public function privacy(): View
    {
        return view('pages.privacy');
    }

    public function contacts(): View
    {
        return view('pages.contacts');
    }
}
