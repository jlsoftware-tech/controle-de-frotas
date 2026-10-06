<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Knuckles\Scribe\Attributes\BodyParam;

/**
 * Request body for resetting the password via token (`POST /api/v1/auth/reset-password`).
 */
#[BodyParam('token', type: 'string', description: 'Token de validação recebido por e-mail para redefinição de senha.', example: 'e7b0a726618c89ffb5c6d328b0f443b73e5f7e6f8812c70da1')]
#[BodyParam('email', type: 'string', description: 'E-mail cadastrado da conta.', example: 'joao.silva@example.com')]
#[BodyParam('password', type: 'string', description: 'Nova senha de acesso (mínimo de 6 caracteres).', example: 'novaSenha@123')]
#[BodyParam('password_confirmation', type: 'string', description: 'Confirmação da nova senha. Deve ser idêntica a `password`.', example: 'novaSenha@123')]
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
            'token' => 'required|string',
            'email' => 'required|string|email',
            'password' => 'required|string|min:6|confirmed',
        ];
    }
}
