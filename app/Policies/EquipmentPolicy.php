<?php

namespace App\Policies;

use App\Models\Equipment;
use App\Models\User;

class EquipmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isProvider() || $user->isAdmin();
    }

    public function view(User $user, Equipment $equipment): bool
    {
        return $this->owns($user, $equipment) || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $profile = $user->providerProfile;

        if (! $profile) {
            return false;
        }

        $max = $profile->planSetting('max_equipment');

        return $max === null || $profile->equipment()->count() < $max;
    }

    public function update(User $user, Equipment $equipment): bool
    {
        return $this->owns($user, $equipment) || $user->isAdmin();
    }

    public function delete(User $user, Equipment $equipment): bool
    {
        return $this->owns($user, $equipment) || $user->isAdmin();
    }

    protected function owns(User $user, Equipment $equipment): bool
    {
        return $user->providerProfile?->id === $equipment->provider_profile_id;
    }
}
