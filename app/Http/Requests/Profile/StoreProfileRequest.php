<?php

namespace App\Http\Requests\Profile;

use App\Http\Requests\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Knuckles\Scribe\Attributes\BodyParam;

/**
 * Request body for creating a profile (`POST /api/v1/profiles`).
 */
#[BodyParam('name', type: 'string', description: 'Nome do perfil de acesso (máximo de 255 caracteres). Deve ser único.', example: 'Administrador')]
#[BodyParam('description', type: 'string', description: 'Descrição do perfil de acesso (máximo de 255 caracteres).', required: false, example: 'Acesso geral ao sistema')]
#[BodyParam('permissions', type: 'integer[]', description: 'IDs das permissões vinculadas ao perfil, sem repetições (ver `GET /api/v1/permissions`).', required: false, example: [1, 2, 3, 4])]
class StoreProfileRequest extends FormRequest
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
            'name' => 'required|unique:profiles|max:255',
            'description' => 'max:255',
            'permissions' => 'array',
            'permissions.*' => 'distinct|integer|exists:permissions,id',
        ];
    }
}
