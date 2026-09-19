<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Knuckles\Scribe\Attributes\BodyParam;

#[BodyParam('email', description: 'Email cadastrado do usuário.', example: 'admin@example.com')]
#[BodyParam('password', description: 'Senha de acesso do usuário.', example: 'senha123')]
#[BodyParam('remember', description: 'Checkbox "Lembrar-me".')]
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

    /**
     * Custom parameter data for Scribe documentation.
     *
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'email' => [
                'description' => 'Email cadastrado do usuário.',
                'example' => 'admin@example.com',
            ],
            'password' => [
                'description' => 'Senha de acesso do usuário.',
                'example' => 'senha123',
            ],
            'remember' => [
                'description' => 'Checkbox "Lembrar-me".',
            ],
        ];
    }
}
