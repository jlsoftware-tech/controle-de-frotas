<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Knuckles\Scribe\Attributes\BodyParam;

#[BodyParam(name: 'name', description: 'nome do perfil de acesso', example: 'admin')]
#[BodyParam(name: 'description', description: 'descrição do perfil de acesso', example: 'acesso geral ao sistema')]
#[BodyParam(name: 'permissions', type: 'integer[]', description: 'lista dos ids das permissões de acesso do perfil', required: false, example: '[1, 2, 3, 4]')]

class UpdateProfileRequest extends FormRequest
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
            'name' => 'required|max:255',
            'description' => 'max:255',
            'permissions' => 'array',
            'permissions.*' => 'distinct|integer|exists:permissions,id',
        ];
    }
}
