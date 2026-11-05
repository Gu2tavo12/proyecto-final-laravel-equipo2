<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends Model
{
    use HasFactory;

    /**
     * Campos que se pueden asignar de forma masiva.
     *
     * [ENCAPSULAMIENTO] Solo estos atributos se aceptan en create()/update();
     * id y timestamps quedan protegidos contra asignacion masiva.
     */
    protected $fillable = [
        'nombre',
        'rubro',
        'correo_contacto',
        'telefono',
    ];

    /**
     * Ofertas publicadas por la empresa.
     *
     * [RELACION] empresas 1:N ofertas
     */
    public function ofertas(): HasMany
    {
        return $this->hasMany(Oferta::class);
    }
}
