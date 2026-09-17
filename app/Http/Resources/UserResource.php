<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
			'profile' => $this->profile ? [
				'id' => $this->profile->id,
				'name' => $this->profile->name,
			] : null,
			'secretariat' => [
				'id' => $this->secretariat_id,
				'name' => $this->secretariat->name,
				'acronym' => $this->secretariat->acronym,
			],
            'created_at' => $this->created_at->format('d/m/Y H:i:s'),
            'updated_at' => $this->updated_at->format('d/m/Y H:i:s'),
            'deleted_at' => $this->deleted_at?->format('d/m/Y H:i:s'),
        ];
    }
}
