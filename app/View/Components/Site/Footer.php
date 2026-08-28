<?php

namespace App\View\Components\Site;

use App\Models\EquipmentCategory;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Долният колонтитул има нужда от списък с категории — заявката стои тук,
 * а не в шаблона, и се кешира.
 */
class Footer extends Component
{
    public function render(): View
    {
        $categories = Cache::remember(
            'footer.categories',
            now()->addHours(6),
            fn () => EquipmentCategory::active()->roots()->take(6)->get()
        );

        return view('components.site.footer', compact('categories'));
    }
}
