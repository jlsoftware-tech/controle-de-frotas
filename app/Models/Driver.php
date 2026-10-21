<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['cpf', 'cnh', 'cnh_expiration_date', 'cnh_category', 'phone_number', 'status', 'user_id'])]
class Driver extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected static function booted(): void
    {
        static::deleting(function (Driver $driver) {
            if (! $driver->isForceDeleting()) {
                $driver->user?->delete();
            } else {
                $driver->user()->withTrashed()->first()?->forceDelete();
            }
        });

        static::restoring(function (Driver $driver) {
            $driver->user()->withTrashed()->first()?->restore();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function request(): BelongsToMany
    {
        return $this->belongsToMany(TransportationRequest::class);
    }

    public function scopeWithUser(Builder $query): Builder
    {
        return $query->join('users', 'users.id', '=', 'drivers.user_id')
            ->select('drivers.*');
    }
}
