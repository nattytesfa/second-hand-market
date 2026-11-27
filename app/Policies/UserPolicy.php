<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Role assignment is a superadmin-only account-management operation.
     * Self-changes and assignment of the seeder-managed superadmin role are
     * always denied, even to a superadmin.
     */
    public function updateRole(User $user, User $target, string $role): bool
    {
        return $user->isSuperAdmin()
            && ! $user->is($target)
            && ! $target->isSuperAdmin()
            && in_array($role, User::MANAGEABLE_ROLES, true);
    }

    /**
     * Placeholder for the future admin account-management screens. No UI for
     * this operation exists in Phase 1.
     */
    public function manageAccount(User $user, User $target): bool
    {
        return $user->isSuperAdmin() && ! $user->is($target);
    }
}
