<?php

namespace App\Http\Requests;

use App\Models\Oferta;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Fluent;
use Illuminate\Validation\Rule;

/**
 * [VALIDACION] Reglas del formulario de ofertas. UpdateOfertaRequest hereda
 * de esta clase para validar igual al crear y al editar.
 */
class StoreOfertaRequest extends FormRequest
{
    /**
     * Tope de decimal(10,2): 8 digitos enteros y 2 decimales.
     */
    public const SALARIO_MAXIMO_PERMITIDO = 99999999.99;

    /**
     * Fase 2 no tiene autenticacion: cualquier visitante puede enviar el formulario.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validacion.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $salario = [
            // [REGLA-NEGOCIO] En empleo ambos salarios son obligatorios;
            // en pasantia se pueden omitir.
            'required_if:tipo,empleo',
            'nullable',
            'numeric',
            'decimal:0,2',
            // [REGLA-NEGOCIO] Salario no negativo y dentro de decimal(10,2).
            'min:0',
            'max:'.self::SALARIO_MAXIMO_PERMITIDO,
        ];

        return [
            'empresa_id' => ['required', 'integer', Rule::exists('empresas', 'id')],
            'titulo' => ['required', 'string', 'max:150'],
            // TEXT guarda hasta 65 535 bytes; con utf8mb4 cada caracter ocupa
            // hasta 4 bytes, asi que 16 000 caracteres siempre caben.
            'descripcion' => ['required', 'string', 'max:16000'],
            'modalidad' => ['required', Rule::in(array_keys(Oferta::MODALIDADES))],
            'tipo' => ['required', Rule::in(array_keys(Oferta::TIPOS))],
            'fecha_publicacion' => ['required', 'date_format:Y-m-d'],
            // [REGLA-NEGOCIO] La fecha de cierre es estrictamente posterior a la publicacion.
            'fecha_cierre' => ['required', 'date_format:Y-m-d', 'after:fecha_publicacion'],
            // [REGLA-NEGOCIO] Al menos una vacante; tope de unsigned smallint.
            'vacantes' => ['required', 'integer', 'between:1,65535'],
            'salario_minimo' => $salario,
            'salario_maximo' => [
                ...$salario,
                // [REGLA-NEGOCIO] Minimo <= maximo solo cuando ambos existen.
                Rule::when(
                    fn (Fluent $input) => filled($input->get('salario_minimo')) && is_numeric($input->get('salario_minimo')),
                    ['gte:salario_minimo'],
                ),
            ],
            // [SEGURIDAD] La imagen se valida por su contenido (MIME detectado
            // con fileinfo), no solo por la extension del nombre.
            'imagen' => [
                'nullable',
                'file',
                'mimetypes:image/jpeg,image/png,image/webp',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
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
            'required_if' => 'El campo :attribute es obligatorio cuando la oferta es de empleo.',
            'string' => 'El campo :attribute debe ser texto.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'numeric' => 'El campo :attribute debe ser un número.',
            'decimal' => 'El campo :attribute admite como máximo 2 decimales.',
            'in' => 'El valor seleccionado en :attribute no es válido.',
            'exists' => 'La :attribute seleccionada no existe.',
            'date_format' => 'El campo :attribute debe ser una fecha válida (AAAA-MM-DD).',
            'titulo.max' => 'El título no puede superar los 150 caracteres.',
            'descripcion.max' => 'La descripción no puede superar los 16 000 caracteres.',
            'fecha_cierre.after' => 'La fecha de cierre debe ser posterior a la fecha de publicación.',
            'vacantes.between' => 'Las vacantes deben estar entre 1 y 65 535.',
            'salario_minimo.min' => 'El salario mínimo no puede ser negativo.',
            'salario_maximo.min' => 'El salario máximo no puede ser negativo.',
            'salario_minimo.max' => 'El salario mínimo no puede superar $99,999,999.99.',
            'salario_maximo.max' => 'El salario máximo no puede superar $99,999,999.99.',
            'salario_maximo.gte' => 'El salario máximo debe ser mayor o igual al salario mínimo.',
            'imagen.file' => 'La imagen debe ser un archivo.',
            'imagen.mimetypes' => 'La imagen debe ser JPG, PNG o WebP.',
            'imagen.mimes' => 'La imagen debe ser JPG, PNG o WebP.',
            'imagen.max' => 'La imagen no puede pesar más de 2 MB.',
            'imagen.uploaded' => 'No se pudo subir la imagen; revisa que no pese más de 2 MB.',
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
            'empresa_id' => 'empresa',
            'titulo' => 'título',
            'descripcion' => 'descripción',
            'modalidad' => 'modalidad',
            'tipo' => 'tipo',
            'fecha_publicacion' => 'fecha de publicación',
            'fecha_cierre' => 'fecha de cierre',
            'vacantes' => 'vacantes',
            'salario_minimo' => 'salario mínimo',
            'salario_maximo' => 'salario máximo',
            'imagen' => 'imagen',
        ];
    }
}
