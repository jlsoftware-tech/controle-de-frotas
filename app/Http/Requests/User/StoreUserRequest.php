<?php

namespace App\Http\Requests\User;

use App\Http\Requests\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Knuckles\Scribe\Attributes\BodyParam;

/**
 * Request body for creating a user (`POST /api/v1/users`).
 */
#[BodyParam('name', type: 'string', description: 'Nome completo do usuário (máximo de 255 caracteres).', example: 'Maria Santos')]
#[BodyParam('email', type: 'string', description: 'E-mail do usuário para autenticação (máximo de 255 caracteres). Deve ser único.', example: 'maria.santos@example.com')]
#[BodyParam('password', type: 'string', description: 'Senha de acesso do usuário (mínimo de 8 caracteres).', example: 'senha12345')]
#[BodyParam('password_confirmation', type: 'string', description: 'Confirmação da senha. Deve ser idêntica a `password`.', example: 'senha12345')]
#[BodyParam('profile_id', type: 'integer', description: 'ID do perfil de acesso atribuído ao usuário.', example: 1)]
#[BodyParam('secretariat_id', type: 'integer', description: 'ID da secretaria vinculada ao usuário.', example: 1)]
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
