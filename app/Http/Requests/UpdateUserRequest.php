<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Knuckles\Scribe\Attributes\BodyParam;

#[BodyParam('name', 'Atualiza o nome', required: false, nullable: true)]
#[BodyParam('email', 'Atualiza o email', required: false, nullable: true)]
#[BodyParam('password', 'Define uma nova senha', required: false, nullable: true)]
#[BodyParam('profile_id', 'Altera o perfil', required: false, nullable: true)]
#[BodyParam('secretariat_id', 'Altera a secretaria', required: false, nullable: true)]
class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'string|max:255',
            'email' => 'email|max:255',
            'password' => 'string|min:8|confirmed',
            'profile_id' => 'integer|exists:profiles,id',
            'secretariat_id' => 'integer|exists:secretariats,id'
        ];
    }
}
