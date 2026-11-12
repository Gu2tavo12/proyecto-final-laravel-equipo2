<?php

namespace App\Http\Requests;

/**
 * [VALIDACION] Al editar se aplican las mismas reglas, mensajes y atributos
 * que al crear (heredados de StoreOfertaRequest). La imagen sigue siendo
 * opcional: si no se adjunta, se conserva la actual.
 */
class UpdateOfertaRequest extends StoreOfertaRequest
{
}
