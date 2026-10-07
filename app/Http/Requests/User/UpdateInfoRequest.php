<?php

namespace App\Http\Requests\User;

use App\Http\Requests\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Knuckles\Scribe\Attributes\BodyParam;

/**
 * Request body for updating the authenticated user's personal data (`PUT /api/v1/users/update`).
 */
#[BodyParam('name', type: 'string', description: 'Nome do usuário (máximo de 255 caracteres).', required: false, example: 'Jorge Luis Fonseca')]
#[BodyParam('email', type: 'string', description: 'E-mail do usuário (máximo de 255 caracteres). Deve ser único entre os demais usuários.', required: false, example: 'jorge_lois@gmail.com')]
class UpdateInfoRequest extends FormRequest
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
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                Rule::unique('users', 'email')
                    ->ignore($this->user('api')),
                'max:255',
            ],
        ];
    }
}
