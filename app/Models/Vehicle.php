<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name',
    'plate',
    'renavam',
    'chassi',
    'brand',
    'model',
    'model_year',
    'fuel_type',
    'tank_capacity',
    'status',
    'secretariat_id',
])]

class Vehicle extends Model
{
    use SoftDeletes;

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
        return $this->hasMany(VehicleMeasure::class);
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
