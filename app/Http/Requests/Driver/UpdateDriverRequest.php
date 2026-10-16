<?php

namespace App\Http\Requests\Driver;

use App\Http\Requests\User\UpdateUserRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class UpdateDriverRequest extends UpdateUserRequest
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
            ...parent::rules(),
            'cpf' => ['sometimes', 'string', 'size:11',
                Rule::unique('drivers', 'cpf')->ignore($this->driver)],
            'cnh' => ['sometimes', 'string', 'size:11',
                Rule::unique('drivers', 'cnh')->ignore($this->driver)],
            'cnh_expiration_date' => ['sometimes', 'date', 'after:today'],
            'cnh_category' => ['sometimes', 'string', 'in:A,BA,B,C,D,E,AB,AC,AD,AE'],
            'phone_number' => ['sometimes', 'string', 'min:8', 'max:14'],
            'status' => ['sometimes', 'string', 'in:VALID,EXPIRED']
        ];
    }
}
