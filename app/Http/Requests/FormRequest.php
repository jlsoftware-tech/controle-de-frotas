<?php

namespace App\Http\Requests;

use App\Support\ApiResponder;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest as BaseFormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Base class for the API requests.
 *
 * Validation failures respond with 422 in the `{success, status_code, message, data}` format, where
 * `data` is keyed by field name and holds the list of error messages.
 */
class FormRequest extends BaseFormRequest
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
            //
        ];
    }

    /**
     * Respond with the API's standard JSON (422) instead of Laravel's default redirect/format.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponder::error(
                'Os dados enviados são inválidos.',
                422,
                $validator->errors()->toArray()
            )
        );
    }

    /**
     * Dados de parâmetros personalizados do corpo da requisição para a documentação do Scribe.
     *
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            //
        ];
    }

    /**
     * Dados de parâmetros personalizados da query string da requisição para a documentação do Scribe.
     *
     * @return array<string, array<string, mixed>>
     */
    public function queryParameters(): array
    {
        return [
            //
        ];
    }
}
