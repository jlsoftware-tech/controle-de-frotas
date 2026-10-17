<?php

namespace App\Http\Requests\Driver;

use App\Models\Driver;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ListDriversRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::guard('api')->user()->can('viewAny', Driver::class);
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
                'in:name,created_at',
            ],
            'order' => ['nullable', 'sometimes', 'string', 'in:desc,asc'],
        ];
    }

    #[\Override()]
    public function prepareForValidation(): void
    {
        $this->merge([
            'page' => $this->input('page', 1),
            'per_page' => $this->input('per_page', 10),
            'sort' => $this->input('sort', 'name'),
            'order' => $this->input('order', 'asc')
        ]);
    }
}
