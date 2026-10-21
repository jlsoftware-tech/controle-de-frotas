<?php

namespace App\Http\Requests\Driver;

use App\Http\Requests\User\UpdateUserRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Knuckles\Scribe\Attributes\BodyParam;

#[BodyParam('name', type: 'string', description: 'Nome completo do motorista (máximo de 255 caracteres).', required: false, example: 'Maria Santos')]
#[BodyParam('email', type: 'string', description: 'E-mail do motorista para autenticação (máximo de 255 caracteres). Deve ser único.', required: false, example: 'maria.santos@example.com')]
#[BodyParam('password', type: 'string', description: 'Senha de acesso do motorista (mínimo de 8 caracteres).', required: false, example: 'senha12345')]
#[BodyParam('password_confirmation', type: 'string', description: 'Confirmação da senha. Deve ser idêntica a `password`.', required: false, example: 'senha12345')]
#[BodyParam('profile_id', type: 'integer', description: 'ID do perfil de acesso atribuído ao motorista.', required: false, example: 1)]
#[BodyParam('secretariat_id', type: 'integer', description: 'ID da secretaria vinculada ao motorista.', required: false, example: 1)]
#[BodyParam('cpf', description: 'CPF do motorista (11 números, sem pontos ou traços). Deve ser único.', required: false, example: '12345678912')]
#[BodyParam('cnh', description: 'CNH do motorista (11 números). Deve ser único.', required: false, example: '12345678912')]
#[BodyParam('cnh_expiration_date', type: 'date', description: 'Data de vencimento da CNH.', required: false, example: '2050-01-01')]
#[BodyParam('cnh_category', description: 'Categoria da CNH.', required: false, example: 'AB', enum: ['A', 'B', 'C', 'D', 'E', 'AB', 'AC', 'AD', 'AE'])]
#[BodyParam('phone_number', description: 'Número de telefone de motorista (entre 8 e 14 dígitos).', required: false, example: '+5588988112233')]
#[BodyParam('status', description: 'Status de atividade do motorista.', required: false, example: 'ACTIVE', enum: ['ACTIVE', 'INACTIVE'])]
class UpdateDriverRequest extends UpdateUserRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::guard('api')->user()->can('update', $this->driver);
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
            'status' => ['sometimes', 'string', 'in:ACTIVE,INACTIVE'],
        ];
    }

    public function messages(): array
    {
        return [
            'cnh_expiration_date.after' => 'A CNH não pode estar vencida.',
        ];
    }
}
