<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ImportarCotizacionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // La autorización se maneja en el controlador mediante el token de integración
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Campos de control obligatorios
            'persona_external_id' => ['required', 'integer', 'min:1'],
            'empresa_activa_external_id' => ['required', 'integer', 'min:1'],
            'token_opt' => ['required', 'string', 'size:20', 'regex:/^[a-zA-Z0-9_]{20}$/'],
            'pedido_id' => ['nullable', 'integer', 'min:1'],
            'pdf_url' => ['nullable', 'string', 'url', 'starts_with:https://'],

            // Datos de identidad de Persona
            'persona_nombre' => ['required', 'string', 'max:100'],
            'persona_apellido' => ['nullable', 'string', 'max:100'],
            'persona_email' => ['nullable', 'email', 'max:150'],
            'persona_dni' => ['nullable', 'string', 'max:20'],
            'persona_genero' => ['nullable', 'string', 'in:M,F,X'],
            'persona_tel' => ['nullable', 'string', 'max:50'],

            // Datos de identidad de Empresa
            'empresa_razon_social' => ['required', 'string', 'max:255'],
            'empresa_cuit' => ['nullable', 'string', 'max:11'],
            'empresa_iva' => ['nullable', 'string', 'max:10'],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'persona_external_id.required' => 'El campo persona_external_id es obligatorio.',
            'persona_external_id.integer' => 'El campo persona_external_id debe ser un número entero.',
            'persona_external_id.min' => 'El campo persona_external_id debe ser mayor a 0.',

            'empresa_activa_external_id.required' => 'El campo empresa_activa_external_id es obligatorio.',
            'empresa_activa_external_id.integer' => 'El campo empresa_activa_external_id debe ser un número entero.',
            'empresa_activa_external_id.min' => 'El campo empresa_activa_external_id debe ser mayor a 0.',

            'token_opt.required' => 'El campo token_opt es obligatorio.',
            'token_opt.string' => 'El campo token_opt debe ser una cadena de texto.',
            'token_opt.size' => 'El campo token_opt debe tener exactamente 20 caracteres.',
            'token_opt.regex' => 'El campo token_opt solo puede contener letras, números y guiones bajos.',

            'pedido_id.integer' => 'El campo pedido_id debe ser un número entero.',
            'pedido_id.min' => 'El campo pedido_id debe ser mayor a 0.',

            'pdf_url.string' => 'El campo pdf_url debe ser una cadena de texto.',
            'pdf_url.url' => 'El campo pdf_url debe ser una URL válida.',
            'pdf_url.starts_with' => 'El campo pdf_url debe comenzar con https://.',

            'persona_nombre.required' => 'El nombre de la persona es obligatorio.',
            'persona_nombre.string' => 'El nombre de la persona debe ser texto.',
            'persona_nombre.max' => 'El nombre de la persona no puede exceder 100 caracteres.',

            'persona_apellido.string' => 'El apellido de la persona debe ser texto.',
            'persona_apellido.max' => 'El apellido de la persona no puede exceder 100 caracteres.',

            'persona_email.email' => 'El email de la persona debe ser válido.',
            'persona_email.max' => 'El email de la persona no puede exceder 150 caracteres.',

            'persona_dni.string' => 'El DNI de la persona debe ser texto.',
            'persona_dni.max' => 'El DNI de la persona no puede exceder 20 caracteres.',

            'persona_genero.string' => 'El género de la persona debe ser texto.',
            'persona_genero.in' => 'El género debe ser M, F o X.',

            'persona_tel.string' => 'El teléfono de la persona debe ser texto.',
            'persona_tel.max' => 'El teléfono de la persona no puede exceder 50 caracteres.',

            'empresa_razon_social.required' => 'La razón social de la empresa es obligatoria.',
            'empresa_razon_social.string' => 'La razón social debe ser texto.',
            'empresa_razon_social.max' => 'La razón social no puede exceder 255 caracteres.',

            'empresa_cuit.string' => 'El CUIT de la empresa debe ser texto.',
            'empresa_cuit.max' => 'El CUIT de la empresa no puede exceder 11 caracteres.',

            'empresa_iva.string' => 'La condición de IVA debe ser texto.',
            'empresa_iva.max' => 'La condición de IVA no puede exceder 10 caracteres.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Los datos proporcionados no cumplen con las reglas de validación.',
                'errors' => $validator->errors()->toArray(),
            ], 422)
        );
    }
}
