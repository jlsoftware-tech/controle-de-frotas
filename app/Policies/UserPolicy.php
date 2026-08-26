<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->permissions->contains(function ($permission, $key) {
            return $permission->name === 'view' && $permission->module === 'user';
        });
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->permissions->contains(function ($permission, $key) {
            return $permission->name === 'view' && $permission->module === 'user';
        });
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->permissions->contains(function ($permission, $key) {
            return $permission->name === 'create' && $permission->module === 'user';
        });
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return $user->permissions->contains(function ($permission, $key) {
            return $permission->name === 'update' && $permission->module === 'user';
        });
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->permissions->contains(function ($permission, $key) {
            return $permission->name === 'delete' && $permission->module === 'user';
        });
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
