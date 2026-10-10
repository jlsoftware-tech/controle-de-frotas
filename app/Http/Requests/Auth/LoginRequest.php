<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Knuckles\Scribe\Attributes\BodyParam;

/**
 * Request body for login (`POST /api/v1/auth/login`).
 */
#[BodyParam('email', type: 'string', description: 'E-mail cadastrado do usuário.', example: 'admin@example.com')]
#[BodyParam('password', type: 'string', description: 'Senha de acesso do usuário.', example: 'senha123')]
#[BodyParam('remember', type: 'boolean', description: 'Quando `true` ("Lembrar-me"), o token emitido expira em 7 dias em vez de 1 dia.', required: false, example: true)]
class LoginRequest extends FormRequest
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
            'email' => 'required|email',
            'password' => 'required|string',
            'remember' => 'boolean',
        ];
    }
}
