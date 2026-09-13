<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vehicle extends Model
{
    public function secretariat(): BelongsTo
    {
        return $this->belongsTo(Secretariat::class);
    }
}
