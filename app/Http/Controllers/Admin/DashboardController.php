<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LeadProviderStatus;
use App\Enums\LeadStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadProvider;
use App\Models\Payment;
use App\Models\ProviderProfile;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $monthStart = now()->startOfMonth();

        $leadsThisMonth = Lead::where('created_at', '>=', $monthStart)->count();
        $validThisMonth = Lead::valid()->where('created_at', '>=', $monthStart)->count();
        $completed = Lead::where('status', LeadStatus::Completed)->where('created_at', '>=', $monthStart)->count();

        $stats = [
            'leads_today' => Lead::whereDate('created_at', today())->count(),
            'leads_month' => $leadsThisMonth,
            'valid_month' => $validThisMonth,
            'providers_active' => ProviderProfile::dispatchable()->count(),
            'providers_new' => ProviderProfile::where('created_at', '>=', $monthStart)->count(),
            'providers_pending' => ProviderProfile::whereNull('approved_at')->whereNotNull('submitted_at')->count(),
            'completed_month' => $completed,
            'revenue_month' => (float) Payment::where('status', PaymentStatus::Paid)
                ->where('paid_at', '>=', $monthStart)
                ->sum('amount'),
            'revenue_pending' => (float) Payment::where('status', PaymentStatus::Pending)->sum('amount'),
            // Дял на заявките, приети от поне един доставчик.
            'conversion_rate' => $validThisMonth > 0
                ? round(LeadProvider::whereIn('status', [
                    LeadProviderStatus::Accepted,
                    LeadProviderStatus::Contacted,
                    LeadProviderStatus::Completed,
                ])->where('created_at', '>=', $monthStart)
                    ->distinct('lead_id')->count('lead_id') / $validThisMonth * 100, 1)
                : 0.0,
        ];

        return view('admin.dashboard', [
            'stats' => $stats,
            'leadsByDay' => $this->leadsByDay(),
            'leadsByCategory' => $this->leadsByCategory(),
            'leadsByCity' => $this->leadsByCity(),
            'topProviders' => $this->topProviders(),
            'latestLeads' => Lead::with(['category', 'city'])->latest()->take(10)->get(),
        ]);
    }

    /** Заявки по дни за последните 30 дни, попълнени и с нулевите дни. */
    protected function leadsByDay(): array
    {
        $rows = Lead::query()
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $series = [];

        for ($date = now()->subDays(29)->startOfDay(); $date <= now(); $date = $date->copy()->addDay()) {
            $key = $date->toDateString();
            $series[$key] = (int) ($rows[$key] ?? 0);
        }

        return $series;
    }

    protected function leadsByCategory()
    {
        return Lead::query()
            ->where('created_at', '>=', now()->subDays(90))
            ->select('equipment_category_id', DB::raw('COUNT(*) as total'))
            ->groupBy('equipment_category_id')
            ->with('category')
            ->orderByDesc('total')
            ->take(8)
            ->get();
    }

    protected function leadsByCity()
    {
        return Lead::query()
            ->where('created_at', '>=', now()->subDays(90))
            ->select('city_id', DB::raw('COUNT(*) as total'))
            ->groupBy('city_id')
            ->with('city')
            ->orderByDesc('total')
            ->take(8)
            ->get();
    }

    protected function topProviders()
    {
        return ProviderProfile::query()
            ->withCount([
                'leadAssignments as leads_count',
                'leadAssignments as accepted_count' => fn ($q) => $q->whereIn('status', [
                    LeadProviderStatus::Accepted,
                    LeadProviderStatus::Contacted,
                    LeadProviderStatus::Completed,
                ]),
            ])
            ->orderByDesc('accepted_count')
            ->take(8)
            ->get();
    }
}
