<?php

namespace App\Http\Requests\User;

use App\Http\Requests\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Knuckles\Scribe\Attributes\BodyParam;

/**
 * Request body for resetting the authenticated user's password (`PUT /api/v1/users/reset-password`).
 */
#[BodyParam('password', type: 'string', description: 'Nova senha do usuário (mínimo de 8 caracteres). Deve ser diferente da senha atual.', example: 'jemzkjcm')]
#[BodyParam('password_confirmation', type: 'string', description: 'Confirmação da nova senha. Deve ser idêntica a `password`.', example: 'jemzkjcm')]
class ResetPasswordRequest extends FormRequest
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
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => 'Informe uma senha',
        ];
    }
}
