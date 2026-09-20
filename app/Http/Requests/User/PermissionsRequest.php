<?php

namespace App\Http\Requests\User;

use App\Http\Requests\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class PermissionsRequest extends FormRequest
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
        $module = $this->request->get('module');

        return [
            'module' => [
                'required',
                'string',
                'in:'.$module,
                'exists:permissions,module',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'module.required' => 'Informe o nome do módulo',
            'module.exists' => 'O módulo '.strtoupper($this->request->get('module')).' não existe',
        ];
    }
}
