<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostulacionRequest;
use App\Http\Requests\UpdatePostulacionRequest;
use App\Models\Oferta;
use App\Models\Postulacion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Postulaciones de candidatos a las ofertas.
 */
class PostulacionController extends Controller
{
    /**
     * Listado paginado de postulaciones, las mas recientes primero, filtrable
     * por oferta (?oferta_id=) y por estado (?estado=).
     *
     * [CRUD-READ] with('oferta.empresa') carga ofertas y empresas en dos consultas
     * adicionales en lugar de una por fila (evita N+1).
     * [INYECCION-DEPENDENCIAS] Laravel inyecta el Request actual.
     */
    public function index(Request $request): View
    {
        $filtros = $this->filtros($request);

        $postulaciones = Postulacion::with('oferta.empresa')
            // [SEGURIDAD] Solo se filtra con valores ya normalizados y como bindings.
            ->when($filtros['oferta_id'] !== null, fn (Builder $query) => $query->where('oferta_id', $filtros['oferta_id']))
            ->when($filtros['estado'] !== '', fn (Builder $query) => $query->where('estado', $filtros['estado']))
            ->latest('fecha_postulacion')
            ->latest('id')
            ->paginate(10)
            // Los enlaces de paginacion conservan oferta_id y estado.
            ->withQueryString();

        $ofertas = Oferta::orderBy('titulo')->get(['id', 'titulo']);

        return view('postulaciones.index', compact('postulaciones', 'ofertas', 'filtros'));
    }

    /**
     * Formulario de registro. Solo ofrece ofertas activas y, si llega
     * ?oferta_id= de una oferta activa (enlace desde la ficha de la oferta),
     * la deja preseleccionada.
     *
     * [CRUD-CREATE]
     * [INYECCION-DEPENDENCIAS] Laravel inyecta el Request actual.
     */
    public function create(Request $request): View
    {
        $ofertas = $this->ofertasActivas();
        $ofertaId = filter_var($request->query('oferta_id'), FILTER_VALIDATE_INT);
        $ofertaSeleccionada = $ofertas->contains('id', $ofertaId) ? $ofertaId : null;

        return view('postulaciones.create', compact('ofertas', 'ofertaSeleccionada'));
    }

    /**
     * Registra la postulacion de un candidato.
     *
     * [CRUD-CREATE]
     * [INYECCION-DEPENDENCIAS] Laravel inyecta StorePostulacionRequest y lo valida
     * antes de ejecutar el metodo; si falla, regresa al formulario con errores y old().
     */
    public function store(StorePostulacionRequest $request): RedirectResponse
    {
        // [SEGURIDAD] Solo datos validados. La fecha y el estado los asigna el
        // servidor: se ignoran aunque el cliente los envie en el POST.
        $datos = [
            ...$request->validated(),
            'fecha_postulacion' => today(),
            'estado' => 'recibida',
        ];

        try {
            $postulacion = Postulacion::create($datos);
        } catch (UniqueConstraintViolationException) {
            // [REGLA-NEGOCIO] Respaldo de la BD: si dos envios iguales pasan la validacion
            // al mismo tiempo, unique(oferta_id, correo) rechaza el segundo.
            return back()
                ->withInput()
                ->withErrors(['correo' => 'Este correo ya está postulado a esta oferta.']);
        }

        return redirect()
            ->route('postulaciones.show', $postulacion)
            ->with('exito', 'Postulación registrada correctamente.');
    }

    /**
     * Ficha de una postulacion con su oferta y la empresa de la oferta.
     *
     * [CRUD-READ]
     * [INYECCION-DEPENDENCIAS] Route model binding: Laravel busca {postulacion}
     * y responde 404 si no existe.
     */
    public function show(Postulacion $postulacion): View
    {
        $postulacion->load('oferta.empresa');

        return view('postulaciones.show', compact('postulacion'));
    }

    /**
     * Formulario de edicion con los datos actuales.
     *
     * [CRUD-UPDATE] Una postulacion historica se puede gestionar aunque su
     * oferta ya este cerrada: la regla de oferta activa solo aplica al registro.
     * [INYECCION-DEPENDENCIAS] Route model binding: 404 si no existe.
     */
    public function edit(Postulacion $postulacion): View
    {
        $postulacion->load('oferta.empresa');

        return view('postulaciones.edit', compact('postulacion'));
    }

    /**
     * Actualiza los datos del candidato y el estado de la postulacion.
     *
     * [CRUD-UPDATE] validated() no incluye oferta_id ni fecha_postulacion:
     * ambas conservan su valor original.
     * [INYECCION-DEPENDENCIAS] UpdatePostulacionRequest (validacion) y Postulacion (binding).
     */
    public function update(UpdatePostulacionRequest $request, Postulacion $postulacion): RedirectResponse
    {
        try {
            $postulacion->update($request->validated());
        } catch (UniqueConstraintViolationException) {
            return back()
                ->withInput()
                ->withErrors(['correo' => 'Este correo ya está postulado a esta oferta.']);
        }

        return redirect()
            ->route('postulaciones.show', $postulacion)
            ->with('exito', 'Postulación actualizada correctamente.');
    }

    /**
     * Elimina solo la postulacion indicada (solo por DELETE).
     *
     * [CRUD-DELETE] La oferta y las demas postulaciones se conservan: la FK
     * cascade va de ofertas a postulaciones, no al reves.
     * [SEGURIDAD] La ruta solo responde a DELETE con token CSRF; un GET a la
     * misma URL muestra la ficha y no borra nada.
     * [INYECCION-DEPENDENCIAS] Route model binding: 404 si no existe.
     */
    public function destroy(Postulacion $postulacion): RedirectResponse
    {
        $ofertaId = $postulacion->oferta_id;
        $postulacion->delete();

        return redirect()
            ->route('postulaciones.index', ['oferta_id' => $ofertaId])
            ->with('exito', 'Postulación de '.$postulacion->nombre_candidato.' eliminada correctamente.');
    }

    /**
     * Ofertas que aun reciben postulaciones (scope activas()), con su empresa.
     *
     * @return Collection<int, Oferta>
     */
    private function ofertasActivas(): Collection
    {
        return Oferta::activas()->with('empresa')->orderBy('titulo')->get();
    }

    /**
     * Normaliza los filtros del GET: solo una oferta existente (id entero) y
     * un estado conocido; cualquier otro valor se ignora.
     *
     * @return array{oferta_id: int|null, estado: string}
     */
    private function filtros(Request $request): array
    {
        $ofertaId = filter_var($request->query('oferta_id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $estado = $request->query('estado');

        return [
            'oferta_id' => $ofertaId !== false && Oferta::whereKey($ofertaId)->exists() ? $ofertaId : null,
            'estado' => is_string($estado) && array_key_exists($estado, Postulacion::ESTADOS) ? $estado : '',
        ];
    }
}
