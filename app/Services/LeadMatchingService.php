<?php

namespace App\Services;

use App\Enums\OperatorRequirement;
use App\Models\Equipment;
use App\Models\Lead;
use App\Models\ProviderProfile;
use App\Services\Matching\MatchResult;
use Illuminate\Support\Collection;

/**
 * Намира и подрежда доставчиците, които са подходящи за дадена заявка.
 *
 * Алгоритъмът е нарочно прозрачен: всеки критерий носи конфигурируем брой
 * точки и връща обяснение, така че администраторът да вижда защо един
 * доставчик е предложен преди друг.
 */
class LeadMatchingService
{
    /**
     * @return Collection<int, MatchResult>
     */
    public function candidates(Lead $lead, ?int $limit = null): Collection
    {
        $limit ??= (int) config('matching.suggested_providers');
        $minimum = (int) config('matching.minimum_score');

        return $this->scoreAll($lead)
            ->filter(fn (MatchResult $result) => $result->score >= $minimum)
            ->take($limit)
            ->values();
    }

    /**
     * Всички допустими доставчици, подредени по резултат — без праг и лимит.
     * Използва се в администрацията, за да се вижда и „втората редица“.
     *
     * @return Collection<int, MatchResult>
     */
    public function scoreAll(Lead $lead): Collection
    {
        return $this->eligibleProviders($lead)
            ->map(fn (ProviderProfile $provider) => $this->score($lead, $provider))
            ->sortByDesc(fn (MatchResult $result) => $result->score)
            ->values();
    }

    /**
     * Предварителен подбор през базата — избягва зареждането на целия регистър.
     *
     * @return Collection<int, ProviderProfile>
     */
    protected function eligibleProviders(Lead $lead): Collection
    {
        return ProviderProfile::query()
            ->dispatchable()
            ->with(['city', 'serviceAreas.city', 'equipment.category', 'subscriptions'])
            ->when($lead->equipment_category_id, function ($query) use ($lead) {
                // Когато категорията е известна, търсим доставчици с такава техника.
                $query->whereHas('equipment', function ($q) use ($lead) {
                    $q->where('active', true)
                        ->where('equipment_category_id', $lead->equipment_category_id);
                });
            })
            ->when(! $lead->equipment_category_id, function ($query) {
                // „Не знам каква техника ми трябва“ — стига доставчикът да има активна техника.
                $query->whereHas('equipment', fn ($q) => $q->where('active', true));
            })
            ->get();
    }

    public function score(Lead $lead, ProviderProfile $provider): MatchResult
    {
        $weights = config('matching.weights');
        $breakdown = [];
        $reasons = [];

        $matchingEquipment = $this->matchingEquipment($lead, $provider);

        if ($lead->equipment_category_id && $matchingEquipment->isNotEmpty()) {
            $breakdown['category'] = (int) $weights['category'];
            $reasons[] = 'Разполага с техника в категория „'.$lead->category?->name.'“';
        }

        if ($lead->equipment_type && $matchingEquipment->contains(fn (Equipment $e) => $e->type === $lead->equipment_type)) {
            $breakdown['equipment_type'] = (int) $weights['equipment_type'];
            $reasons[] = 'Има точния тип: '.$lead->equipment_type;
        }

        [$locationPoints, $locationReasons, $distance] = $this->scoreLocation($lead, $provider, $weights);
        $breakdown = array_merge($breakdown, $locationPoints);
        $reasons = array_merge($reasons, $locationReasons);

        if ($this->operatorMatches($lead, $provider, $matchingEquipment)) {
            $breakdown['operator_match'] = (int) $weights['operator_match'];
            $reasons[] = 'Покрива изискването за оператор ('.$lead->operator_required->shortLabel().')';
        }

        if ($provider->verified) {
            $breakdown['verified'] = (int) $weights['verified'];
            $reasons[] = 'Проверен доставчик';
        }

        if ($provider->rating_count > 0) {
            $ratingPoints = (int) round(($provider->rating_avg / 5) * (int) $weights['rating']);

            if ($ratingPoints > 0) {
                $breakdown['rating'] = $ratingPoints;
                $reasons[] = 'Оценка '.$provider->displayRating().' от '.$provider->rating_count.' отзива';
            }
        }

        $planBonus = (int) (config('matching.plan_bonus.'.$provider->plan()->value) ?? 0);

        if ($planBonus > 0) {
            $breakdown['plan'] = $planBonus;
            $reasons[] = 'План '.$provider->plan()->label();
        }

        return new MatchResult(
            provider: $provider,
            score: (int) array_sum($breakdown),
            breakdown: $breakdown,
            reasons: $reasons,
            distanceKm: $distance,
        );
    }

    /**
     * @return array{0: array<string, int>, 1: array<int, string>, 2: float|null}
     */
    protected function scoreLocation(Lead $lead, ProviderProfile $provider, array $weights): array
    {
        $points = [];
        $reasons = [];
        $distance = $lead->city && $provider->city ? $provider->city->distanceTo($lead->city) : null;

        if (! $lead->city_id) {
            return [$points, $reasons, $distance];
        }

        if ($provider->city_id === $lead->city_id) {
            $points['same_city'] = (int) $weights['same_city'];
            $reasons[] = 'Базиран в '.$lead->city->name;

            return [$points, $reasons, $distance];
        }

        $serviceArea = $provider->serviceAreas->firstWhere('city_id', $lead->city_id);

        if ($serviceArea) {
            $points['service_area'] = (int) $weights['service_area'];
            $reasons[] = 'Обслужва '.$lead->city->name;
        }

        if ($distance !== null) {
            $radius = $provider->service_radius ?: (int) config('matching.default_radius_km');

            if ($distance <= $radius) {
                $points['within_radius'] = (int) $weights['within_radius'];
                $reasons[] = 'В радиуса на обслужване ('.number_format($distance, 0, ',', ' ').' км от '.$provider->city?->name.')';
            }
        }

        if (! isset($points['same_city']) && $provider->city?->region === $lead->city->region) {
            $points['same_region'] = (int) $weights['same_region'];
            $reasons[] = 'В същата област ('.$lead->city->region.')';
        }

        return [$points, $reasons, $distance];
    }

    /**
     * @return Collection<int, Equipment>
     */
    protected function matchingEquipment(Lead $lead, ProviderProfile $provider): Collection
    {
        return $provider->equipment
            ->where('active', true)
            ->when(
                $lead->equipment_category_id,
                fn (Collection $items) => $items->where('equipment_category_id', $lead->equipment_category_id)
            )
            ->values();
    }

    /**
     * @param  Collection<int, Equipment>  $equipment
     */
    protected function operatorMatches(Lead $lead, ProviderProfile $provider, Collection $equipment): bool
    {
        $pool = $equipment->isNotEmpty() ? $equipment : $provider->equipment->where('active', true);

        return match ($lead->operator_required) {
            OperatorRequirement::Required => $pool->contains(fn (Equipment $e) => $e->operator_available),
            OperatorRequirement::NotRequired => $pool->contains(fn (Equipment $e) => ! $e->operator_only),
            OperatorRequirement::Any, OperatorRequirement::Unknown => $pool->isNotEmpty(),
        };
    }
}
