<?php

namespace App\Http\Controllers;

use App\Models\Oferta;
use App\Models\Postulacion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
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
