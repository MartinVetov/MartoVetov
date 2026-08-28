<?php

namespace App\Services;

use App\Enums\LeadProviderStatus;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\LeadProvider;
use App\Models\ProviderProfile;
use App\Notifications\LeadAssignedToProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Изпращане на заявка към избрани доставчици и записване на историята.
 */
class LeadDispatchService
{
    public function __construct(
        protected LeadMatchingService $matching,
        protected AnalyticsService $analytics,
    ) {}

    /**
     * @param  array<int, int>  $providerIds
     * @return Collection<int, LeadProvider> Само новосъздадените изпращания.
     */
    public function send(Lead $lead, array $providerIds): Collection
    {
        $providers = ProviderProfile::query()
            ->dispatchable()
            ->whereIn('id', $providerIds)
            ->with('user')
            ->get();

        $scores = $this->matching->scoreAll($lead)->keyBy(fn ($result) => $result->provider->id);

        $created = DB::transaction(function () use ($lead, $providers, $scores) {
            $created = collect();

            foreach ($providers as $provider) {
                // Една заявка се изпраща веднъж към даден доставчик.
                if ($lead->assignments()->where('provider_profile_id', $provider->id)->exists()) {
                    continue;
                }

                $created->push(LeadProvider::create([
                    'lead_id' => $lead->id,
                    'provider_profile_id' => $provider->id,
                    'status' => LeadProviderStatus::Sent,
                    'sent_at' => now(),
                    'price' => $lead->leadPrice(),
                    'match_score' => $scores->get($provider->id)?->score,
                ]));
            }

            if ($created->isNotEmpty()) {
                $lead->forceFill([
                    'sent_count' => $lead->assignments()->count(),
                    'status' => in_array($lead->status, [LeadStatus::New, LeadStatus::Verified], true)
                        ? LeadStatus::Sent
                        : $lead->status,
                ])->save();
            }

            return $created;
        });

        foreach ($created as $assignment) {
            $provider = $providers->firstWhere('id', $assignment->provider_profile_id);
            $provider?->user?->notify(new LeadAssignedToProvider($lead, $assignment));

            $this->analytics->record(AnalyticsService::LEAD_SENT, $lead, [
                'provider_id' => $provider?->id,
                'match_score' => $assignment->match_score,
            ]);
        }

        return $created;
    }

    /** Автоматично изпращане към най-подходящите доставчици (изключено по подразбиране). */
    public function autoDispatch(Lead $lead): Collection
    {
        if (! config('matching.auto_dispatch')) {
            return collect();
        }

        $ids = $this->matching->candidates($lead)->map(fn ($r) => $r->provider->id)->all();

        return $ids ? $this->send($lead, $ids) : collect();
    }
}
