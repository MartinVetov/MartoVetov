<?php

namespace App\Enums;

enum SubscriptionPlan: string
{
    case Free = 'free';
    case Pro = 'pro';
    case Business = 'business';

    public function label(): string
    {
        return match ($this) {
            self::Free => 'Безплатен',
            self::Pro => 'Про',
            self::Business => 'Бизнес',
        };
    }

    /** Брой заявки на месец; null означава без ограничение. */
    public function monthlyLeadQuota(): ?int
    {
        return config("nt.plans.{$this->value}.monthly_lead_quota");
    }
}
