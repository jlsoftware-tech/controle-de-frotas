<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'plate' => $this->plate,
            'renavan' => $this->renavan,
            'chassi' => $this->chassi,
            'brand' => $this->brand,
            'model' => $this->model,
            'model_year' => $this->model_year,
            'fuel_type' => $this->fuel_type,
            'tank_capacity' => $this->tank_capacity,
            'status' => $this->status,
            'secretariat_id' => $this->secretariat_id,
            'created_at' => $this->created_at->format('d/m/Y H:m:s'),
            'updated_at' => $this->updated_at->format('d/m/Y H:m:s'),
            'deleted_at' => $this->deleted_at?->format('d/m/Y H:m:s'),
        ];
    }
}
