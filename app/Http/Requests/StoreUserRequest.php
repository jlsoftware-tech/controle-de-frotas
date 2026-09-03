<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Knuckles\Scribe\Attributes\BodyParam;

#[BodyParam(name: 'name', description: 'Nome do novo usuário.', required: true)]
#[BodyParam(name: 'email', description: 'E-mail do usuário.', required: true)]
#[BodyParam(name: 'password', description: 'Senha de acesso á aplicação.', required: true)]
#[BodyParam(name: 'profile_id', description: 'Permissões do usuário.', required: true)]
#[BodyParam(name: 'secretariat_id', description: 'Secretaria a qual o usuário pertence.', required: true)]
class StoreUserRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'profile_id' => 'required|integer|exists:profiles,id',
            'secretariat_id' => 'required|integer|exists:secretariats,id',
        ];
    }
}
