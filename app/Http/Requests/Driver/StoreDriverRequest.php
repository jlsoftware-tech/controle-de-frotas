<?php

namespace App\Http\Requests\Driver;

use App\Http\Requests\User\StoreUserRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDriverRequest extends StoreUserRequest
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
            'cpf' => ['required', 'unique:drivers', 'string', 'size:11'],
            'cnh' => ['required', 'unique:drivers', 'string', 'size:11'],
            'cnh_expiration_date' => ['required', 'date', 'after:today'],
            'cnh_category' => ['required', 'string', 'in:A,BA,B,C,D,E,AB,AC,AD,AE'],
            'phone_number' => ['required', 'string', 'min:10', 'max:11'],
            'status' => ['sometimes', 'string', 'in:VALID,INVALID']
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'status' => $this->input('status', 'VALID')
        ]);
    }
}
