<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[Fillable(['name', 'module'])]
class Permission extends Model
{
    public function profiles(): BelongsToMany
    {
        return $this->belongsToMany(Profile::class, 'profile_permission');
    }

    public function users(): HasManyThrough
    {
        return $this->through('profiles')->has('users');
    }
}
