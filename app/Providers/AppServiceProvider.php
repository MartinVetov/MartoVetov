<?php

namespace App\Providers;

use App\Models\Equipment;
use App\Models\Lead;
use App\Models\ProviderProfile;
use App\Policies\EquipmentPolicy;
use App\Policies\LeadPolicy;
use App\Policies\ProviderProfilePolicy;
use App\Services\Payments\PaymentManager;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentManager::class);
    }

    public function boot(): void
    {
        Gate::policy(Equipment::class, EquipmentPolicy::class);
        Gate::policy(Lead::class, LeadPolicy::class);
        Gate::policy(ProviderProfile::class, ProviderProfilePolicy::class);

        // Ограничение срещу спам заявки: по IP и по телефон.
        RateLimiter::for('leads', fn (Request $request) => [
            Limit::perHour(5)->by($request->ip()),
            Limit::perDay(10)->by((string) $request->input('contact_phone', $request->ip())),
        ]);

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
