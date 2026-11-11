<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

/**
 * [VALIDACION] Reglas para editar una empresa: las mismas del registro, pero la
 * unicidad ignora a la empresa que se esta editando.
 */
class UpdateEmpresaRequest extends StoreEmpresaRequest
{
    public function rules(): array
    {
        // Modelo obtenido por route model binding ({empresa})
        $empresa = $this->route('empresa');

        return array_merge(parent::rules(), [
            'nombre' => ['required', 'string', 'max:150', Rule::unique('empresas', 'nombre')->ignore($empresa)],
            'correo_contacto' => ['required', 'email', 'max:150', Rule::unique('empresas', 'correo_contacto')->ignore($empresa)],
        ]);
    }
}
