<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Knuckles\Scribe\Attributes\BodyParam;

#[BodyParam('name', description: 'Nome completo do usuário.', example: 'Maria Santos')]
#[BodyParam('email', description: 'E-mail do usuário para autenticação.', example: 'maria.santos@example.com')]
#[BodyParam('password', description: 'Senha de acesso do usuário (mínimo de 8 caracteres).', example: 'senha12345')]
#[BodyParam('password_confirmation', description: 'Confirmação da senha de acesso.', example: 'senha12345')]
#[BodyParam('profile_id', description: 'ID do perfil de acesso atribuído ao usuário.', example: 1)]
#[BodyParam('secretariat_id', description: 'ID da secretaria vinculada ao usuário.', example: 1)]
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

    /**
     * Custom parameter data for Scribe documentation.
     *
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'Nome completo do usuário.',
                'example' => 'Maria Santos',
            ],
            'email' => [
                'description' => 'E-mail do usuário para autenticação.',
                'example' => 'maria.santos@example.com',
            ],
            'password' => [
                'description' => 'Senha de acesso do usuário (mínimo de 8 caracteres).',
                'example' => 'senha12345',
            ],
            'password_confirmation' => [
                'description' => 'Confirmação da senha de acesso.',
                'example' => 'senha12345',
            ],
            'profile_id' => [
                'description' => 'ID do perfil de acesso atribuído ao usuário.',
                'example' => 1,
            ],
            'secretariat_id' => [
                'description' => 'ID da secretaria vinculada ao usuário.',
                'example' => 1,
            ],
        ];
    }
}
