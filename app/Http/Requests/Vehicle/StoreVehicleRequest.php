<?php

namespace App\Http\Requests\Vehicle;

use App\Http\Requests\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Knuckles\Scribe\Attributes\BodyParam;

#[BodyParam('name', description: 'Nome comercial do veículo.', example: 'Chevrolet Onix 1.0')]
#[BodyParam('plate', description: 'Identificação da placa. É permitido o padrão antigo ABC1234, e o novo padrão ABC1D23.', required: false, example: 'ABC1D23')]
#[BodyParam('renavam', description: 'Número do Renavam do veículo.', required: false, example: '01234567890')]
#[BodyParam('chassi', description: 'Identificação do Chassi do veículo.', required: false, example: '9BWZZZ377VT004251')]
#[BodyParam('brand', description: 'Marca do veículo.', example: 'Chevrolet')]
#[BodyParam('model', description: 'Modelo do veículo.', example: 'Onix')]
#[BodyParam('model_year', description: 'Ano do modelo do veículo.', example: '2024')]
#[BodyParam('fuel_type', description: 'Tipo do combustível do veículo.', required: false, example: 'flex')]
#[BodyParam('tank_capacity', description: 'Capacidade do tanque de combustível do veículo.', required: false, example: '44 L')]
#[BodyParam('status', description: 'Status da disponibilidade do veículo. Valor padrão: AVAILABLE', required: false, example: 'AVAILABLE')]
#[BodyParam('secretariat_id', description: 'ID da secretaria a qual o veículo pertence.', example: '3')]
class StoreVehicleRequest extends FormRequest
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
            'name' => 'required|string|min:3',
            'plate' => 'nullable|alpha_num|unique:vehicles,plate|regex:/^[A-Z]{3}[0-9][A-Z0-9][0-9]{2}$/',
            'renavam' => 'nullable|numeric|digits:11|unique:vehicles,renavam',
            'chassi' => 'nullable|unique:vehicles,chassi|regex:/^[A-Z0-9]{17}$/',
            'brand' => 'required|string',
            'model' => 'required|string|min:3',
            'model_year' => 'required|int|min:1950|max:'.now()->year,
            'fuel_type' => 'nullable|in:GASOLINE,DIESEL,ETHANOL,ELECTRICITY',
            'tank_capacity' => 'nullable|int|between:1,200',
            'status' => 'in:AVAILABLE,UNAVAILABLE',
            'secretariat_id' => 'required|integer|exists:secretariats,id',
        ];
    }

    /**
     * Prepare the data for validation.
     * Sets default values for pagination and ordering if not provided.
     */
    #[\Override()]
    public function prepareForValidation(): void
    {
        $name = strtolower($this->input('name'));
        $name = array_filter(explode(' ', $name));

        $prepared = [
            'name' => implode(' ', $name),
            'plate' => strtoupper($this->input('plate')),
            'chassi' => strtoupper($this->input('chassi')),
            'fuel_type' => strtoupper($this->input('fuel_type')),
            'status' => strtoupper($this->input('status')),
        ];

        $this->merge(array_filter($prepared));
    }

    public function messages(): array
    {
        return [
            'plate.unique' => 'A placa já está sendo utilizada.',
            'plate.regex' => 'A placa deve estar no formato ABC1234 ou ABC1D23.',
            'chassi.regex' => 'O campo chassi deve conter 17 caracteres alfanuméricos. Carácter acentuado também é inválido.',
            'secretariat_id.exists' => 'A secretaria selecionada é inválida.',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            //
        ];
    }
}
