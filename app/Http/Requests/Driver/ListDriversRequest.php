<?php

namespace App\Http\Requests\Driver;

use App\Models\Driver;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Knuckles\Scribe\Attributes\QueryParam;

/**
 * Query parameters for listing driver (`GET /api/v1/drivers`).
 */
#[QueryParam('page', type: 'integer', description: 'Número da página atual para paginação dos resultados. Padrão: 1.', required: false, example: 1, nullable: true)]
#[QueryParam('per_page', type: 'integer', description: 'Quantidade de registros retornados por página (mínimo: 1, máximo: 100). Padrão: 10.', required: false, example: 10, nullable: true)]
#[QueryParam('search', type: 'string', description: 'Termo para busca por parte do nome do motorista.', required: false, example: 'João', nullable: true)]
#[QueryParam('sort', type: 'string', description: 'Campo utilizado para ordenação dos resultados. Padrão: `name`.', required: false, example: 'name', enum: ['name', 'email', 'created_at'], nullable: true)]
#[QueryParam('order', type: 'string', description: 'Direção da ordenação dos resultados (`asc` ou `desc`). Padrão: `asc`.', required: false, example: 'desc', enum: ['asc', 'desc'], nullable: true)]
#[QueryParam('status', type: 'string', description: 'Filtra os motoristas pelo seu status. Padrão: `ACTIVE`.', required: false, example: 'INACTIVE', enum: ['ALL', 'ACTIVE', 'INACTIVE'], nullable: true)]
#[QueryParam('cnh_category', type: 'string', description: 'Filtra os motoristas pela categoria da cnh. Padrão `ALL`', required: false, example: 'AB', enum: ['ALL','A','B','C','D','E','AB','AC','AD','AE'], nullable: true)]
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
            'status' => ['nullable', 'sometimes', 'string', 'in:ALL,INACTIVE,ACTIVE'],
            'cnh_category' => ['nullable', 'sometimes', 'string', 'in:ALL,A,B,C,D,E,AB,AC,AD,AE']
        ];
    }

    #[\Override()]
    public function prepareForValidation(): void
    {
        $this->merge([
            'page' => $this->input('page', 1),
            'per_page' => $this->input('per_page', 10),
            'sort' => $this->input('sort', 'name'),
            'order' => $this->input('order', 'asc'),
            'status' => strtoupper($this->input('status', 'ACTIVE')),
            'cnh_category' => strtoupper($this->input('cnh_category', 'ALL'))
        ]);
    }

    public function messages()
    {
        return [
            'cnh_category.in' => 'A categoria de CNH selecionada é inválida',
        ];
    }
}
