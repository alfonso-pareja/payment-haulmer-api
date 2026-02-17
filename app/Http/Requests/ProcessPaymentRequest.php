<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class ProcessPaymentRequest extends FormRequest
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
     */
    public function rules(): array
    {
       return [
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999.99',
            ],
            'currency' => [
                'required',
                'string',
                'size:3',
                'regex:/^[A-Z]{3}$/',
            ],
            'cardNumber' => [
                'required',
                'string',
                'regex:/^\d{13,19}$/',
            ],
            'cardHolder' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[a-zA-Z\s]+$/'
            ],
        ];
    }

    /**
     * Get custom error messages
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'El monto es obligatorio.',
            'amount.numeric' => 'El monto debe ser un número válido.',
            'amount.min' => 'El monto debe ser al menos 0.01.',
            'amount.max' => 'El monto no puede exceder 999,999.99.',

            'currency.required' => 'El campo moneda es obligatorio.',
            'currency.size' => 'La moneda debe tener exactamente 3 caracteres (formato ISO 4217).',
            'currency.regex' => 'La moneda debe estar en formato ISO 4217 en mayúsculas (ej., USD, EUR).',

            'cardNumber.required' => 'El número de tarjeta es obligatorio.',
            'cardNumber.regex' => 'El número de tarjeta debe contener solo 13-19 dígitos.',

            'cardHolder.required' => 'El nombre del titular de la tarjeta es obligatorio.',
            'cardHolder.min' => 'El nombre del titular de la tarjeta debe tener al menos 3 caracteres.',
            'cardHolder.max' => 'El nombre del titular de la tarjeta no puede exceder 100 caracteres.',
            'cardHolder.regex' => 'El nombre del titular de la tarjeta solo puede contener letras y espacios.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Validation Failed',
            'errors' => $validator->errors(),
        ], 422));
    }
}
