<?php

namespace App\Policies;

use App\Models\Secretariat;
use App\Models\User;

class SecretariatPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->permissions->contains(function ($permission, $key) {
            return $permission->action === 'view' && $permission->module === 'secretariats';
        });
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ?Secretariat $secretariat = null): bool
    {
        return $user->permissions->contains(function ($permission, $key) {
            return $permission->action === 'view' && $permission->module === 'secretariats';
        });
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->permissions->contains(function ($permission, $key) {
            return $permission->action === 'create' && $permission->module === 'secretariats';
        });
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ?Secretariat $secretariat = null): bool
    {
        return $user->permissions->contains(function ($permission, $key) {
            return $permission->action === 'update' && $permission->module === 'secretariats';
        });
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ?Secretariat $secretariat = null): bool
    {
        return $user->permissions->contains(function ($permission, $key) {
            return $permission->action === 'delete' && $permission->module === 'secretariats';
        });
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ?Secretariat $secretariat = null): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ?Secretariat $secretariat = null): bool
    {
        return false;
    }
}
