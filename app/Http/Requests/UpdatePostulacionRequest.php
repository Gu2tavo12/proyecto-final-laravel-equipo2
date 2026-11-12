<?php

namespace App\Http\Requests;

use App\Models\Postulacion;
use Illuminate\Validation\Rule;

/**
 * [VALIDACION] Reglas para editar una postulacion: los mismos datos del
 * candidato mas el estado. La oferta y la fecha no se validan ni se aceptan,
 * asi que conservan sus valores originales aunque el cliente los envie.
 */
class UpdatePostulacionRequest extends StorePostulacionRequest
{
    /**
     * Reglas de validacion.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var Postulacion $postulacion Modelo obtenido por route model binding ({postulacion}) */
        $postulacion = $this->route('postulacion');

        return [
            // La unicidad se busca en la oferta de la postulacion e ignora el propio registro.
            ...$this->reglasCandidato($postulacion->oferta_id, $postulacion),
            // [REGLA-NEGOCIO] Solo estados conocidos del modelo.
            'estado' => ['required', Rule::in(array_keys(Postulacion::ESTADOS))],
        ];
    }
}
