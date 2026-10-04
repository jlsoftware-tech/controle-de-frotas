<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
            'name' => 'required|string|min:3|unique:vehicles,name',
            'plate' => 'nullable|alpha_num|min:7|max:7|unique:vehicles,plate|regex:/^[A-Z]{3}[0-9][A-Z0-9][0-9]{2}$/',
            'renavan' => 'nullable|numeric|min:11|max:11|unique:vehicles,renavan',
            'chassi' => 'nullable|alpha_num|min:17|max:17|unique:vehicles,chassi',
            'brand' => 'required|string',
            'model' => 'required|string|min:3',
            'model_year' => 'required|int|min:1950|max:' . now()->year,
            'fuel_type' => 'nullable|string|in:GASOLINE,DIESEL,ETHANOL,ELECTRICITY',
            'tank_capacity' => 'nullable|numeric|min:1',
            'status' => 'string|in:AVAILABLE,UNAVAILABLE',
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
        $name = strtolower($this->input('name') ?? '');
        $name = array_filter(explode(' ', $name));

        $this->merge([
            'name' => implode(' ', $name),
            'plate' => strtoupper($this->input('plate')),
        ]);
    }

    public function messages(): array
    {
        return [
            'plate.regex' => 'A placa deve estar no formato ABC1234 ou ABC1D23.',
        ];
    }
}
