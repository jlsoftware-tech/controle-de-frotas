<?php

namespace App\Http\Requests\Secretariat;

use App\Http\Requests\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Knuckles\Scribe\Attributes\QueryParam;
use Override;

#[QueryParam('page', type: 'integer', description: 'Número da página atual para paginação dos resultados.', required: false, example: 1, nullable: true)]
#[QueryParam('per_page', type: 'integer', description: 'Quantidade de registros retornados por página (mínimo: 1, máximo: 100).', required: false, example: 10, nullable: true)]
#[QueryParam('search', type: 'string', description: 'Termo para busca e filtragem por nome da secretaria.', required: false, example: 'João', nullable: true)]
#[QueryParam('sort', type: 'string', description: 'Campo utilizado para ordenação dos resultados.', required: false, example: 'name', enum: ['name', 'acronym', 'created_at'], nullable: true)]
#[QueryParam('order', type: 'string', description: 'Direção da ordenação dos resultados.', required: false, example: 'desc', enum: ['asc', 'desc'], nullable: true)]
class ListSecretariatRequest extends FormRequest
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
                'in:name,acronym,created_at',
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
