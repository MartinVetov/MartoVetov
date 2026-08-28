<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\EquipmentCategory;
use App\Models\ProviderProfile;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $categories = EquipmentCategory::active()->roots()->get();
        $cities = City::withLanding()->get();
        $providers = ProviderProfile::dispatchable()->get(['slug', 'updated_at']);

        $content = view('sitemap', compact('categories', 'cities', 'providers'))->render();

        return response($content, 200, ['Content-Type' => 'application/xml']);
    }

    /**
     * robots.txt се обслужва динамично, за да сочи винаги към правилния домейн.
     */
    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            '',
            'Disallow: /admin',
            'Disallow: /profil',
            'Disallow: /vhod',
            'Disallow: /registraciya',
            'Disallow: /zaiavka/gotovo',
            'Disallow: /otziv',
            '',
            'Sitemap: '.route('sitemap'),
        ];

        return response(implode(PHP_EOL, $lines), 200, ['Content-Type' => 'text/plain']);
    }
}
