<?php

namespace App\Http\Requests\Driver;

use App\Http\Requests\User\StoreUserRequest;
use App\Models\Driver;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Knuckles\Scribe\Attributes\BodyParam;

/**
 * Request body for creating a driver (`POST /api/v1/drivers`).
 */
#[BodyParam('name', type: 'string', description: 'Nome completo do motorista (máximo de 255 caracteres).', example: 'Maria Santos')]
#[BodyParam('email', type: 'string', description: 'E-mail do motorista para autenticação (máximo de 255 caracteres). Deve ser único.', example: 'maria.santos@example.com')]
#[BodyParam('password', type: 'string', description: 'Senha de acesso do motorista (mínimo de 8 caracteres).', example: 'senha12345')]
#[BodyParam('password_confirmation', type: 'string', description: 'Confirmação da senha. Deve ser idêntica a `password`.', example: 'senha12345')]
#[BodyParam('profile_id', type: 'integer', description: 'ID do perfil de acesso atribuído ao motorista.', example: 1)]
#[BodyParam('secretariat_id', type: 'integer', description: 'ID da secretaria vinculada ao motorista.', example: 1)]
#[BodyParam('cpf', description: 'CPF do motorista (11 números, sem pontos ou traços). Deve ser único.', example: '12345678912')]
#[BodyParam('cnh', description: 'CNH do motorista (11 números). Deve ser único.', example: '12345678912')]
#[BodyParam('cnh_expiration_date', type: 'date', description: 'Data de vencimento da CNH.', example: '2050-01-01')]
#[BodyParam('cnh_category', description: 'Categoria da CNH.', example: 'AB', enum: ['A', 'B', 'C', 'D', 'E', 'AB', 'AC', 'AD', 'AE'])]
#[BodyParam('phone_number', description: 'Número de telefone de motorista (entre 8 e 14 dígitos).', example: '+5588988112233')]
#[BodyParam('status', description: 'Status de atividade do motorista.', required: false, example: 'ACTIVE', enum: ['ACTIVE', 'INACTIVE'])]
class StoreDriverRequest extends StoreUserRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::guard('api')->user()->can('create', Driver::class);
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
            'cnh_category' => ['required', 'string', 'in:A,B,C,D,E,AB,AC,AD,AE'],
            'phone_number' => ['required', 'string', 'min:8', 'max:14'],
            'status' => ['sometimes', 'string', 'in:ACTIVE,INACTIVE'],
            'profile_id' => ['required', 'exists:profiles,id'],
            'secretariat_id' => ['required', 'exists:secretariats,id'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'status' => $this->input('status', 'ACTIVE'),
        ]);
    }

    public function messages(): array
    {
        return [
            'cnh_expiration_date.after:today' => 'A CNH não pode estar vencida.',
        ];
    }
}
