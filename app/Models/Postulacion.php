<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Postulacion extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla. El pluralizador de Laravel es en ingles y
     * generaria "postulacions", por eso se indica explicitamente.
     */
    protected $table = 'postulaciones';

    /**
     * Estados posibles de una postulacion (valor en BD => etiqueta para mostrar).
     */
    public const ESTADOS = [
        'recibida' => 'Recibida',
        'en_revision' => 'En revisión',
        'aceptada' => 'Aceptada',
        'rechazada' => 'Rechazada',
    ];

    /**
     * Campos que se pueden asignar de forma masiva.
     *
     * [ENCAPSULAMIENTO] Solo estos atributos se aceptan en create()/update();
     * id y timestamps quedan protegidos contra asignacion masiva.
     */
    protected $fillable = [
        'oferta_id',
        'nombre_candidato',
        'correo',
        'carrera',
        'enlace_cv',
        'fecha_postulacion',
        'estado',
    ];

    /**
     * Conversion de tipos de los atributos.
     *
     * [ENCAPSULAMIENTO] La fecha de postulacion se maneja como Carbon.
     */
    protected function casts(): array
    {
        return [
            'fecha_postulacion' => 'date',
        ];
    }

    /**
     * Accessor enlace_cv_seguro: el enlace del CV solo si es una URL HTTP/HTTPS;
     * en otro caso (javascript:, data:, texto suelto...) devuelve null.
     *
     * [SEGURIDAD] Blade escapa el texto, pero no impide un href "javascript:...";
     * las vistas usan este accessor para no generar enlaces peligrosos.
     */
    protected function enlaceCvSeguro(): Attribute
    {
        return Attribute::get(function (): ?string {
            $esquema = strtolower((string) parse_url((string) $this->enlace_cv, PHP_URL_SCHEME));

            return in_array($esquema, ['http', 'https'], true)
                && filter_var($this->enlace_cv, FILTER_VALIDATE_URL) !== false
                    ? $this->enlace_cv
                    : null;
        });
    }

    /**
     * Oferta a la que pertenece la postulacion.
     *
     * [RELACION] postulaciones N:1 ofertas
     */
    public function oferta(): BelongsTo
    {
        return $this->belongsTo(Oferta::class);
    }
}
