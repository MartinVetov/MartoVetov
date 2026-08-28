<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Lead $lead): bool
    {
        if ($user->isAdmin() || $user->id === $lead->customer_id) {
            return true;
        }

        // Доставчикът вижда заявката само ако тя му е изпратена.
        return $user->isProvider()
            && $user->providerProfile !== null
            && $lead->assignments()
                ->where('provider_profile_id', $user->providerProfile->id)
                ->exists();
    }

    public function update(User $user, Lead $lead): bool
    {
        return $user->isAdmin();
    }

    public function dispatchToProviders(User $user, Lead $lead): bool
    {
        return $user->isAdmin();
    }
}
