<?php

namespace App\Http\Requests\User;

use App\Http\Requests\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Knuckles\Scribe\Attributes\BodyParam;

#[BodyParam('name', description: 'Nome completo do usuário.', example: 'Maria Santos Silva', required: false)]
#[BodyParam('email', description: 'E-mail do usuário.', example: 'maria.silva@example.com', required: false)]
#[BodyParam('password', description: 'Nova senha de acesso (mínimo de 8 caracteres).', example: 'novaSenha123', required: false)]
#[BodyParam('password_confirmation', description: 'Confirmação da nova senha de acesso.', example: 'novaSenha123', required: false)]
#[BodyParam('profile_id', description: 'ID do perfil de acesso atribuído ao usuário.', example: 1, required: false)]
#[BodyParam('secretariat_id', description: 'ID da secretaria vinculada ao usuário.', example: 1, required: false)]
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
            'email' => [
                'email',
                Rule::unique('users', 'email')
                    ->ignore($this->route('user')?->id ?? $this->route('id')),
                'max:255',
            ],
            'password' => 'string|min:8|confirmed',
            'profile_id' => 'integer|exists:profiles,id',
            'secretariat_id' => 'integer|exists:secretariats,id',
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
                'example' => 'Maria Santos Silva',
            ],
            'email' => [
                'description' => 'E-mail do usuário.',
                'example' => 'maria.silva@example.com',
            ],
            'password' => [
                'description' => 'Nova senha de acesso (mínimo de 8 caracteres).',
                'example' => 'novaSenha123',
            ],
            'password_confirmation' => [
                'description' => 'Confirmação da nova senha de acesso.',
                'example' => 'novaSenha123',
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
