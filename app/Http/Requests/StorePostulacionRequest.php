<?php

namespace App\Http\Requests;

use App\Models\Oferta;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * [VALIDACION] Reglas para registrar un candidato en una oferta.
 * UpdatePostulacionRequest reutiliza los campos del candidato y los mensajes.
 */
class StorePostulacionRequest extends FormRequest
{
    /**
     * Fase 2 no tiene autenticacion: cualquier visitante puede postularse.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normaliza el correo antes de validar: sin espacios y en minusculas, asi
     * "Ana@Correo.com " y "ana@correo.com" cuentan como el mismo candidato.
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('correo'))) {
            $this->merge(['correo' => mb_strtolower(trim($this->input('correo')))]);
        }
    }

    /**
     * Reglas de validacion.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'oferta_id' => [
                'bail',
                'required',
                'integer',
                Rule::exists('ofertas', 'id'),
                // [REGLA-NEGOCIO] No se aceptan postulaciones en ofertas cerradas. Se valida
                // la oferta real del POST (no la que se mostro en el formulario) y se reutiliza
                // estaActiva(): una oferta que cierra hoy sigue aceptando postulaciones.
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! Oferta::findOrFail($value)->estaActiva()) {
                        $fail('La oferta seleccionada ya cerró y no recibe postulaciones.');
                    }
                },
            ],
            ...$this->reglasCandidato(),
        ];
    }

    /**
     * Reglas de los datos del candidato. El correo es unico dentro de la oferta
     * (la restriccion unique(oferta_id, correo) de la BD queda como respaldo).
     *
     * @return array<string, array<int, mixed>>
     */
    protected function reglasCandidato(?int $ofertaId = null, mixed $ignorar = null): array
    {
        $ofertaId ??= filter_var($this->input('oferta_id'), FILTER_VALIDATE_INT) ?: 0;

        return [
            'nombre_candidato' => ['required', 'string', 'max:150'],
            'correo' => [
                'required',
                'string',
                'email',
                'max:150',
                // [REGLA-NEGOCIO] Un mismo correo no puede postularse dos veces a la misma oferta.
                Rule::unique('postulaciones', 'correo')
                    ->where(fn ($query) => $query->where('oferta_id', $ofertaId))
                    ->ignore($ignorar),
            ],
            'carrera' => ['required', 'string', 'max:100'],
            // [SEGURIDAD] Solo URLs http/https: se rechazan javascript:, data:, ftp:, etc.
            'enlace_cv' => ['required', 'string', 'max:255', 'url:http,https'],
        ];
    }

    /**
     * Mensajes en espanol.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe ser texto.',
            'integer' => 'Selecciona una :attribute válida.',
            'exists' => 'La :attribute seleccionada no existe.',
            'in' => 'El :attribute seleccionado no es válido.',
            'nombre_candidato.max' => 'El nombre no puede superar los 150 caracteres.',
            'correo.email' => 'Ingresa un correo electrónico válido.',
            'correo.max' => 'El correo no puede superar los 150 caracteres.',
            'correo.unique' => 'Este correo ya está postulado a esta oferta.',
            'carrera.max' => 'La carrera no puede superar los 100 caracteres.',
            'enlace_cv.url' => 'El enlace del CV debe ser una URL válida que empiece con http:// o https://.',
            'enlace_cv.max' => 'El enlace del CV no puede superar los 255 caracteres.',
        ];
    }

    /**
     * Nombres de los campos en espanol para los mensajes.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'oferta_id' => 'oferta',
            'nombre_candidato' => 'nombre del candidato',
            'correo' => 'correo',
            'carrera' => 'carrera',
            'enlace_cv' => 'enlace del CV',
            'estado' => 'estado',
        ];
    }
}
