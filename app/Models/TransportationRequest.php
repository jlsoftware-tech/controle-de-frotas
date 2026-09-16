<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TransportationRequest extends Model
{
    public function vehicle(): BelongsToMany
    {
        return $this->belongsToMany(Vehicle::class);
    }

    public function driver(): BelongsToMany
    {
        return $this->belongsToMany(Driver::class);
    }

    public function requesting_secretariat(): BelongsTo
    {
        return $this->belongsTo(Secretariat::class, 'requesting_secretariat_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
