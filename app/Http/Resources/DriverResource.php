<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
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
            'cpf' => $this->cpf,
            'status' => $this->status,
            'name' => $this->user->name,
            'email' => $this->user->email,
            'cnh' => $this->cnh,
            'cnh_category' => $this->cnh_category,
            'cnh_expiration_date' => $this->cnh_expiration_date,
            'phone_number' => $this->phone_number,
            'profile' => $this->user->profile ? [
                'id' => $this->user->profile->id,
                'name' => $this->user->profile->name,
            ] : null,
            'secretariat' => [
                'id' => $this->user->secretariat_id,
                'name' => $this->user->secretariat->name,
                'acronym' => $this->user->secretariat->acronym,
            ],
            'created_at' => $this->created_at->format('d/m/Y H:i:s'),
            'updated_at' => $this->updated_at->format('d/m/Y H:i:s'),
            'deleted_at' => $this->deleted_at?->format('d/m/Y H:i:s'),
        ];
    }
}
