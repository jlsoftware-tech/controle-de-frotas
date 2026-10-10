<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Knuckles\Scribe\Attributes\QueryParam;

/**
 * Query parameters for the authenticated user's permissions (`GET /api/v1/users/permissions`).
 *
 * Extends Laravel's own `FormRequest`: validation errors use the framework's default format (`message` and `errors`).
 */
#[QueryParam('modules', type: 'string[]', description: 'Nomes dos módulos que serão retornados (`users`, `profiles`, `secretariats`). Se não informado, todos os módulos são retornados. Formato: `modules[]=users&modules[]=profiles`.', required: false, example: ['users', 'profiles'], nullable: true)]
class ListUserPermissionsRequest extends FormRequest
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
            'modules' => ['sometimes', 'list'],
        ];
    }
}
