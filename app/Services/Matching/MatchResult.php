<?php

namespace App\Services\Matching;

use App\Models\ProviderProfile;

/**
 * Резултат от оценяването на един доставчик спрямо конкретна заявка.
 */
class MatchResult
{
    /**
     * @param  array<string, int>  $breakdown  Точки по критерий.
     * @param  array<int, string>  $reasons  Обяснения на български за администратора.
     */
    public function __construct(
        public readonly ProviderProfile $provider,
        public readonly int $score,
        public readonly array $breakdown = [],
        public readonly array $reasons = [],
        public readonly ?float $distanceKm = null,
    ) {}

    public function distanceLabel(): ?string
    {
        return $this->distanceKm === null ? null : number_format($this->distanceKm, 0, ',', ' ').' км';
    }
}
