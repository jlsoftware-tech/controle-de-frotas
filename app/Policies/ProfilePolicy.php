<?php

namespace App\Policies;

use App\Models\Profile;
use App\Models\User;

class ProfilePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->permissions->contains(function ($permission) {
            return $permission->name === 'view' && $permission->module === 'profiles';
        });
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Profile $profile): bool
    {
        return $user->permissions->contains(function ($permission, $key) {
            return $permission->name === 'view' && $permission->module === 'profiles';
        });
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->permissions->contains(function ($permission, $key) {
            return $permission->name === 'create' && $permission->module === 'profiles';
        });
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Profile $profile): bool
    {
        return $user->permissions->contains(function ($permission, $key) {
            return $permission->name === 'update' && $permission->module === 'profiles';
        });
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Profile $profile): bool
    {
        return $user->permissions->contains(function ($permission, $key) {
            return $permission->name === 'delete' && $permission->module === 'profiles';
        });
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Profile $profile): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Profile $profile): bool
    {
        return false;
    }
}
