<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Knuckles\Scribe\Attributes\BodyParam;

#[BodyParam('name', description: 'Nome completo do usuário.', example: 'João Silva')]
#[BodyParam('email', description: 'Email do usuário (deve ser único).', example: 'joao.silva@example.com')]
#[BodyParam('password', description: 'Senha de acesso do usuário (mínimo de 6 caracteres).', example: 'senha@123')]
#[BodyParam('password_confirmation', description: 'Confirmação da senha do usuário (deve ser idêntica ao campo password).', example: 'senha@123')]
#[BodyParam('secretariat_id', type: 'int', description: 'ID da secretaria vinculada ao usuário.', example: 1)]
#[BodyParam('profile_id', type: 'int', description: 'ID do perfil de acesso do usuário.', example: 1)]
class RegisterRequest extends FormRequest
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
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed',
            'secretariat_id' => 'required|integer',
            'profile_id' => 'required|integer',
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
                'example' => 'João Silva',
            ],
            'email' => [
                'description' => 'Email do usuário (deve ser único).',
                'example' => 'joao.silva@example.com',
            ],
            'password' => [
                'description' => 'Senha de acesso do usuário (mínimo de 6 caracteres).',
                'example' => 'senha@123',
            ],
            'password_confirmation' => [
                'description' => 'Confirmação da senha do usuário (deve ser idêntica ao campo password).',
                'example' => 'senha@123',
            ],
            'secretariat_id' => [
                'description' => 'ID da secretaria vinculada ao usuário.',
                'example' => 1,
            ],
            'profile_id' => [
                'description' => 'ID do perfil de acesso do usuário.',
                'example' => 1,
            ],
        ];
    }
}
