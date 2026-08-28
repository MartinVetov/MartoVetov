<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CityController as AdminCityController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LeadController as AdminLeadController;
use App\Http\Controllers\Admin\ProviderController as AdminProviderController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Provider\DashboardController as ProviderDashboardController;
use App\Http\Controllers\Provider\EquipmentController as ProviderEquipmentController;
use App\Http\Controllers\Provider\LeadController as ProviderLeadController;
use App\Http\Controllers\Provider\ProfileController as ProviderProfileController;
use App\Http\Controllers\Provider\ServiceAreaController as ProviderServiceAreaController;
use App\Http\Controllers\ProviderDirectoryController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Публична част
|--------------------------------------------------------------------------
*/

Route::get('/', HomeController::class)->name('home');

Route::get('/kak-raboti', [PageController::class, 'howItWorks'])->name('how-it-works');
Route::get('/za-dostavchitsi', [PageController::class, 'forProviders'])->name('for-providers');
Route::get('/obshti-usloviya', [PageController::class, 'terms'])->name('terms');
Route::get('/politika-poveritelnost', [PageController::class, 'privacy'])->name('privacy');
Route::get('/kontakti', [PageController::class, 'contacts'])->name('contacts');

// Заявка — основният продукт.
Route::get('/zaiavka', [LeadController::class, 'create'])->name('leads.create');
Route::post('/zaiavka', [LeadController::class, 'store'])
    ->middleware('throttle:leads')
    ->name('leads.store');
Route::get('/zaiavka/gotovo/{lead:reference}', [LeadController::class, 'thanks'])->name('leads.thanks');

// SEO страници по категория и град.
Route::get('/tehnika', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/tehnika/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
// Градът не е вложен ресурс на категорията — изключваме автоматичния scoped binding.
Route::get('/tehnika/{category:slug}/{city:slug}', [CategoryController::class, 'showInCity'])
    ->withoutScopedBindings()
    ->name('categories.city');

// Публичен каталог на доставчиците.
Route::get('/dostavchitsi', [ProviderDirectoryController::class, 'index'])->name('providers.index');
Route::get('/dostavchik/{provider:slug}', [ProviderDirectoryController::class, 'show'])->name('providers.show');

// Отзив след завършена работа — по подписан линк от имейла.
Route::get('/otziv/{assignment}', [ReviewController::class, 'create'])
    ->middleware('signed')
    ->name('reviews.create');
Route::post('/otziv/{assignment}', [ReviewController::class, 'store'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('reviews.store');

Route::post('/sabitie', AnalyticsController::class)
    ->middleware('throttle:30,1')
    ->name('analytics.track');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

/*
|--------------------------------------------------------------------------
| Вход и регистрация
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/vhod', [LoginController::class, 'create'])->name('login');
    Route::post('/vhod', [LoginController::class, 'store'])->middleware('throttle:6,1');
    Route::get('/registraciya', [RegisterController::class, 'create'])->name('register');
    Route::post('/registraciya', [RegisterController::class, 'store'])->middleware('throttle:6,1');
});

Route::post('/izhod', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Профил на доставчика
|--------------------------------------------------------------------------
*/

Route::prefix('profil')
    ->name('provider.')
    ->middleware(['auth', 'role:provider'])
    ->group(function () {
        Route::get('/sazdai', [ProviderProfileController::class, 'create'])->name('profile.create');
        Route::post('/sazdai', [ProviderProfileController::class, 'store'])->name('profile.store');

        Route::middleware('provider.profile')->group(function () {
            Route::get('/', ProviderDashboardController::class)->name('dashboard');

            Route::get('/nastroiki', [ProviderProfileController::class, 'edit'])->name('profile.edit');
            Route::put('/nastroiki', [ProviderProfileController::class, 'update'])->name('profile.update');
            Route::post('/izprati-za-odobrenie', [ProviderProfileController::class, 'submit'])->name('profile.submit');

            Route::get('/tehnika', [ProviderEquipmentController::class, 'index'])->name('equipment.index');
            Route::get('/tehnika/dobavi', [ProviderEquipmentController::class, 'create'])->name('equipment.create');
            Route::post('/tehnika', [ProviderEquipmentController::class, 'store'])->name('equipment.store');
            Route::get('/tehnika/{equipment}/redakciya', [ProviderEquipmentController::class, 'edit'])->name('equipment.edit');
            Route::put('/tehnika/{equipment}', [ProviderEquipmentController::class, 'update'])->name('equipment.update');
            Route::delete('/tehnika/{equipment}', [ProviderEquipmentController::class, 'destroy'])->name('equipment.destroy');
            Route::delete('/tehnika/snimka/{image}', [ProviderEquipmentController::class, 'destroyImage'])->name('equipment.images.destroy');

            Route::get('/raioni', [ProviderServiceAreaController::class, 'edit'])->name('areas.edit');
            Route::put('/raioni', [ProviderServiceAreaController::class, 'update'])->name('areas.update');

            Route::get('/zaiavki', [ProviderLeadController::class, 'index'])->name('leads.index');
            Route::get('/zaiavki/{assignment}', [ProviderLeadController::class, 'show'])->name('leads.show');
            Route::patch('/zaiavki/{assignment}', [ProviderLeadController::class, 'update'])->name('leads.update');
        });
    });

/*
|--------------------------------------------------------------------------
| Администрация
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');

        Route::get('/zaiavki', [AdminLeadController::class, 'index'])->name('leads.index');
        Route::get('/zaiavki/{lead}', [AdminLeadController::class, 'show'])->name('leads.show');
        Route::patch('/zaiavki/{lead}', [AdminLeadController::class, 'update'])->name('leads.update');
        Route::post('/zaiavki/{lead}/izprati', [AdminLeadController::class, 'send'])->name('leads.send');

        Route::get('/dostavchici', [AdminProviderController::class, 'index'])->name('providers.index');
        // В администрацията свързваме по id — slug-овете се редактират оттук.
        Route::get('/dostavchici/{provider:id}', [AdminProviderController::class, 'show'])->name('providers.show');
        Route::patch('/dostavchici/{provider:id}', [AdminProviderController::class, 'update'])->name('providers.update');

        Route::get('/kategorii', [AdminCategoryController::class, 'index'])->name('categories.index');
        Route::get('/kategorii/nova', [AdminCategoryController::class, 'create'])->name('categories.create');
        Route::post('/kategorii', [AdminCategoryController::class, 'store'])->name('categories.store');
        Route::get('/kategorii/{category:id}/redakciya', [AdminCategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/kategorii/{category:id}', [AdminCategoryController::class, 'update'])->name('categories.update');

        Route::get('/gradove', [AdminCityController::class, 'index'])->name('cities.index');
        Route::get('/gradove/nov', [AdminCityController::class, 'create'])->name('cities.create');
        Route::post('/gradove', [AdminCityController::class, 'store'])->name('cities.store');
        Route::get('/gradove/{city:id}/redakciya', [AdminCityController::class, 'edit'])->name('cities.edit');
        Route::put('/gradove/{city:id}', [AdminCityController::class, 'update'])->name('cities.update');

        Route::get('/otzivi', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::patch('/otzivi/{review}', [AdminReviewController::class, 'update'])->name('reviews.update');
    });
