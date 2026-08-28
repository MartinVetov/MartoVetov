<?php

namespace App\Policies;

use App\Models\ProviderProfile;
use App\Models\User;

class ProviderProfilePolicy
{
    public function view(?User $user, ProviderProfile $profile): bool
    {
        if ($profile->isDispatchable()) {
            return true;
        }

        return $user !== null && ($user->isAdmin() || $user->id === $profile->user_id);
    }

    public function update(User $user, ProviderProfile $profile): bool
    {
        return $user->isAdmin() || $user->id === $profile->user_id;
    }

    /** Проверката и одобрението са само права на администратора. */
    public function moderate(User $user): bool
    {
        return $user->isAdmin();
    }
}
