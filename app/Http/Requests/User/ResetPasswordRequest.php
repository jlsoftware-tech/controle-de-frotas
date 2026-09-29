<?php

namespace App\Http\Requests\User;

use App\Http\Requests\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Knuckles\Scribe\Attributes\QueryParam;

#[QueryParam('password', description: 'Nova senha do usuário', required: false, example: 'jemzkjcm')]
#[QueryParam('password_confirmation', description: 'Confirmação da nova senha do usuário', required: false, example: 'jemzkjcm')]
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

    public function bodyParameters(): array
    {
        return [];
    }
}
