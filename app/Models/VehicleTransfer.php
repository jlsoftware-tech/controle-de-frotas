<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleTransfer extends Model
{
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function requested_secretariat(): BelongsTo
    {
        return $this->belongsTo(Secretariat::class, 'requested_secretariat_id');
    }

    public function requesting_secretariat(): BelongsTo
    {
        return $this->belongsTo(Secretariat::class, 'requesting_secretariat_id');
    }
}
