<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class SettingsPolicy
{
    public function viewAny(User $user): bool
    {
        return UserRole::tryFrom((int) $user->role) === UserRole::ADMIN;
    }

    public function update(User $user): bool
    {
        return UserRole::tryFrom((int) $user->role) === UserRole::ADMIN;
    }
}
