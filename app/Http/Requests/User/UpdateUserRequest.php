<?php

namespace App\Http\Requests\User;

use App\Http\Requests\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Knuckles\Scribe\Attributes\BodyParam;

/**
 * Request body for updating a user (`PUT|PATCH /api/v1/users/{user}`). All fields are optional.
 */
#[BodyParam('name', type: 'string', description: 'Nome completo do usuário (máximo de 255 caracteres).', required: false, example: 'Maria Santos Silva')]
#[BodyParam('email', type: 'string', description: 'E-mail do usuário (máximo de 255 caracteres). Deve ser único entre os demais usuários.', required: false, example: 'maria.silva@example.com')]
#[BodyParam('password', type: 'string', description: 'Nova senha de acesso (mínimo de 8 caracteres). Exige `password_confirmation`.', required: false, example: 'novaSenha123')]
#[BodyParam('password_confirmation', type: 'string', description: 'Confirmação da nova senha. Obrigatória quando `password` é informada.', required: false, example: 'novaSenha123')]
#[BodyParam('profile_id', type: 'integer', description: 'ID do perfil de acesso atribuído ao usuário.', required: false, example: 1)]
#[BodyParam('secretariat_id', type: 'integer', description: 'ID da secretaria vinculada ao usuário.', required: false, example: 1)]
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
}
