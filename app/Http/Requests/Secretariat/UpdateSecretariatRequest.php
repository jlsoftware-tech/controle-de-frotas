<?php

namespace App\Http\Requests\Secretariat;

use App\Http\Requests\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Knuckles\Scribe\Attributes\BodyParam;

/**
 * Request body for updating a secretariat (`PUT|PATCH /api/v1/secretariats/{secretariat}`). All fields are optional.
 */
#[BodyParam('name', type: 'string', description: 'Nome da secretaria (máximo de 255 caracteres).', required: false, example: 'Secretaria de Administração')]
#[BodyParam('acronym', type: 'string', description: 'Sigla da secretaria (máximo de 16 caracteres).', required: false, example: 'SECAD')]
class UpdateSecretariatRequest extends FormRequest
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
            'acronym' => 'string|max:16',
        ];
    }
}
