<?php

namespace App\Http\Requests\Vehicle;

use App\Http\Requests\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Override;

class ListVehicleRequest extends FormRequest
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
            'page' => ['nullable', 'sometimes', 'integer', 'min:1'],
            'per_page' => ['nullable', 'sometimes', 'integer', 'min:1', 'max:100'],
            'search' => ['nullable', 'sometimes', 'string'],
            'sort' => [
                'nullable',
                'sometimes',
                'string',
                'in:name,plate,model,model_year,status,secretariat_id,created_at',
            ],
            'order' => ['nullable', 'sometimes', 'string', 'in:desc,asc'],
        ];
    }

    /**
     * Prepare the data for validation.
     * Sets default values for pagination and ordering if not provided.
     */
    #[Override()]
    public function prepareForValidation(): void
    {
        $this->merge([
            'page' => $this->input('page', 1),
            'per_page' => $this->input('per_page', 10),
            'sort' => $this->input('sort', 'name'),
            'order' => $this->input('order', 'asc'),
        ]);
    }
}
