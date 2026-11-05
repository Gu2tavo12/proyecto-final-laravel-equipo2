<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Oferta extends Model
{
    use HasFactory;

    /**
     * Modalidades permitidas (valor en BD => etiqueta para mostrar).
     */
    public const MODALIDADES = [
        'presencial' => 'Presencial',
        'remota' => 'Remota',
        'hibrida' => 'Híbrida',
    ];

    /**
     * Tipos de oferta permitidos (valor en BD => etiqueta para mostrar).
     */
    public const TIPOS = [
        'empleo' => 'Empleo',
        'pasantia' => 'Pasantía',
    ];

    /**
     * Campos que se pueden asignar de forma masiva.
     *
     * [ENCAPSULAMIENTO] Solo estos atributos se aceptan en create()/update();
     * id y timestamps quedan protegidos contra asignacion masiva.
     */
    protected $fillable = [
        'empresa_id',
        'titulo',
        'descripcion',
        'modalidad',
        'tipo',
        'salario_minimo',
        'salario_maximo',
        'fecha_publicacion',
        'fecha_cierre',
        'vacantes',
        'imagen',
    ];

    /**
     * Conversion de tipos de los atributos.
     *
     * [ENCAPSULAMIENTO] El modelo controla como se leen y guardan los datos:
     * fechas como Carbon, salarios con 2 decimales y vacantes como entero.
     */
    protected function casts(): array
    {
        return [
            'fecha_publicacion' => 'date',
            'fecha_cierre' => 'date',
            'salario_minimo' => 'decimal:2',
            'salario_maximo' => 'decimal:2',
            'vacantes' => 'integer',
        ];
    }

    /**
     * Empresa que publica la oferta.
     *
     * [RELACION] ofertas N:1 empresas
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Postulaciones recibidas por la oferta.
     *
     * [RELACION] ofertas 1:N postulaciones
     */
    public function postulaciones(): HasMany
    {
        return $this->hasMany(Postulacion::class);
    }

    /**
     * Scope local: ofertas cuya fecha de cierre es hoy o posterior.
     * Uso: Oferta::activas()->get()
     *
     * Se usa la convencion clasica de Laravel (prefijo "scope") en lugar del
     * atributo #[Scope] para que sea facil de identificar por el nombre.
     */
    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('fecha_cierre', '>=', today());
    }

    /**
     * Indica si la oferta sigue abierta (fecha de cierre hoy o posterior).
     *
     * [ENCAPSULAMIENTO] La regla de negocio queda dentro del modelo.
     */
    public function estaActiva(): bool
    {
        return $this->fecha_cierre !== null && $this->fecha_cierre->gte(today());
    }

    /**
     * Accessor imagen_url: URL publica de la imagen o la imagen por defecto.
     *
     * [ENCAPSULAMIENTO] Las vistas no necesitan saber donde se guarda la imagen.
     */
    protected function imagenUrl(): Attribute
    {
        return Attribute::get(fn () => $this->imagen !== null
            ? asset('storage/'.$this->imagen)
            : asset('img/oferta-default.svg'));
    }

    /**
     * Accessor rango_salarial: "$800.00 - $1,200.00", un solo valor
     * o "A convenir" si no hay salario definido.
     *
     * [ENCAPSULAMIENTO] El formato del salario se resuelve en el modelo.
     */
    protected function rangoSalarial(): Attribute
    {
        return Attribute::get(function () {
            $formato = fn ($valor) => '$'.number_format((float) $valor, 2);

            if ($this->salario_minimo !== null && $this->salario_maximo !== null) {
                return $formato($this->salario_minimo).' - '.$formato($this->salario_maximo);
            }

            if ($this->salario_minimo !== null) {
                return $formato($this->salario_minimo);
            }

            if ($this->salario_maximo !== null) {
                return $formato($this->salario_maximo);
            }

            return 'A convenir';
        });
    }
}
