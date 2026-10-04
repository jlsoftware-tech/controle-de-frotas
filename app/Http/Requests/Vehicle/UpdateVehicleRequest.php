<?php

namespace App\Http\Requests\Vehicle;

use App\Http\Requests\FormRequest;
use App\Models\Vehicle;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class UpdateVehicleRequest extends FormRequest
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
        $vehicle = Vehicle::findOrFail($this->route('vehicle')?->id);

        return [
            'name' => [
                'sometimes',
                'string',
                'min:3',
                Rule::unique('vehicles', 'name')->ignore($vehicle),
            ],
            'plate' => [
                'sometimes',
                'nullable',
                'alpha_num',
                'min:7',
                'max:7',
                'regex:/^[A-Z]{3}[0-9][A-Z0-9][0-9]{2}$/',
                Rule::unique('vehicles', 'plate')->ignore($vehicle),
            ],
            'renavan' => [
                'sometimes',
                'nullable',
                'numeric',
                'min:11',
                'max:11',
                Rule::unique('vehicles', 'renavan')->ignore($vehicle),
            ],
            'chassi' => [
                'sometimes',
                'nullable',
                'alpha_num',
                'min:17',
                'max:17',
                Rule::unique('vehicles', 'chassi')->ignore($vehicle),
            ],
            'brand' => 'sometimes|string',
            'model' => 'sometimes|string|min:3',
            'model_year' => 'sometimes|int|min:1950|max:' . now()->year,
            'fuel_type' => 'sometimes|nullable|string|in:GASOLINE,DIESEL,ETHANOL,ELECTRICITY',
            'tank_capacity' => 'sometimes|nullable|numeric|min:1',
            'status' => 'string|in:AVAILABLE,UNAVAILABLE',
            'secretariat_id' => 'sometimes|integer|exists:secretariats,id',
        ];
    }

    /**
     * Prepare the data for validation.
     * Sets default values for pagination and ordering if not provided.
     */
    #[\Override()]
    public function prepareForValidation(): void
    {
        if (!$this->has('name') || !$this->has('plate')) return;

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
