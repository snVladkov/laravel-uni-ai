<?php

namespace App\Policies;

use App\Models\User;

class UserManagementPolicy
{
    /**
     * Determine whether the user can browse users.
     */
    public function viewAny(User $user): bool
    {
        return $this->isAdministrator($user);
    }

    /**
     * Determine whether the user can create users.
     */
    public function create(User $user): bool
    {
        return $this->isAdministrator($user);
    }

    /**
     * Determine whether the user can update the given user.
     */
    public function update(User $user, User $target): bool
    {
        return $this->isAdministrator($user);
    }

    /**
     * Determine whether the user can sync the given user's roles.
     */
    public function syncRoles(User $user, User $target): bool
    {
        return $this->isAdministrator($user);
    }

    /**
     * Check that the user is an administrator with the user management permission.
     */
    private function isAdministrator(User $user): bool
    {
        return $user->hasRole('administrator')
            && $user->can('users.manage');
    }
}
