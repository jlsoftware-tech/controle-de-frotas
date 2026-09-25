<?php

namespace App\Http\Requests\User;

use App\Http\Requests\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Knuckles\Scribe\Attributes\QueryParam;

#[QueryParam('name', description: 'Nome do usuário', required: false, example: ['Jorge Luis Fonseca', 'Augusto Souza da Silva'], nullable: true)]
#[QueryParam('email', description: 'E-mail do usuário', required: false, example: ['jorge_lois@gmail.com', 'augusto_souza@gmail.com'], nullable: true)]
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

    public function bodyParameters(): array
    {
        return [];
    }
}
