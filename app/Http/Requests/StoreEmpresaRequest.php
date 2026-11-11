<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * [VALIDACION] Reglas para registrar una empresa del catalogo.
 */
class StoreEmpresaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:150', 'unique:empresas,nombre'],
            'rubro' => ['required', 'string', 'max:100'],
            'correo_contacto' => ['required', 'email', 'max:150', 'unique:empresas,correo_contacto'],
            'telefono' => ['required', 'string', 'regex:/^\d{4}-\d{4}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la empresa es obligatorio.',
            'nombre.max' => 'El nombre no puede superar los 150 caracteres.',
            'nombre.unique' => 'Ya existe una empresa registrada con ese nombre.',
            'rubro.required' => 'El rubro es obligatorio.',
            'rubro.max' => 'El rubro no puede superar los 100 caracteres.',
            'correo_contacto.required' => 'El correo de contacto es obligatorio.',
            'correo_contacto.email' => 'El correo de contacto no tiene un formato válido.',
            'correo_contacto.max' => 'El correo no puede superar los 150 caracteres.',
            'correo_contacto.unique' => 'Ya existe una empresa registrada con ese correo.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.regex' => 'El teléfono debe tener el formato 0000-0000.',
        ];
    }
}
