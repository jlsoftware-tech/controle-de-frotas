<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    public function secretariat(): BelongsTo
    {
        return $this->belongsTo(Secretariat::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(VehicleDocument::class);
    }

    public function measures(): HasMany
    {
        return $this->hasMany(VehicleDocument::class);
    }

    public function tires(): HasMany
    {
        return $this->hasMany(Tire::class);
    }

    public function tire_history(): HasMany
    {
        return $this->hasMany(TireVehicleHistory::class);
    }

    public function request(): BelongsToMany
    {
        return $this->belongsToMany(TransportationRequest::class);
    }

    public function vehicle_transfers(): HasMany
    {
        return $this->hasMany(VehicleTransfer::class);
    }
}
