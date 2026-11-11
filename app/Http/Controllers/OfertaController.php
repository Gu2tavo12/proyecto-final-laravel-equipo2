<?php

namespace App\Http\Controllers;

use App\Models\Oferta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Consulta de ofertas (Fase 1): listado paginado y ficha.
 * create, store, edit, update y destroy se implementan en la Fase 2.
 */
class OfertaController extends Controller
{
    /**
     * Longitud maxima del texto de busqueda.
     */
    private const BUSQUEDA_MAX = 100;

    /**
     * Listado paginado de ofertas, las mas recientes primero, con busqueda
     * por titulo o empresa y filtros por modalidad y tipo.
     *
     * [CRUD-READ] with('empresa') carga las empresas en una sola consulta (evita N+1).
     */
    public function index(Request $request): View
    {
        $filtros = $this->filtros($request);

        $ofertas = Oferta::with('empresa')
            // [SEGURIDAD] Los valores viajan como parametros enlazados (bindings), nunca
            // concatenados en el SQL. El grupo OR va entre parentesis para que los
            // filtros de modalidad y tipo se apliquen tambien a las coincidencias por empresa.
            ->when($filtros['q'] !== '', function (Builder $query) use ($filtros) {
                $query->where(function (Builder $grupo) use ($filtros) {
                    $grupo->where('titulo', 'like', '%'.$filtros['q'].'%')
                        ->orWhereHas('empresa', fn (Builder $empresa) => $empresa->where('nombre', 'like', '%'.$filtros['q'].'%'));
                });
            })
            ->when($filtros['modalidad'] !== '', fn (Builder $query) => $query->where('modalidad', $filtros['modalidad']))
            ->when($filtros['tipo'] !== '', fn (Builder $query) => $query->where('tipo', $filtros['tipo']))
            ->latest('fecha_publicacion')
            ->latest('id')
            ->paginate(10)
            // Los enlaces de paginacion conservan q, modalidad y tipo.
            ->withQueryString();

        return view('ofertas.index', compact('ofertas', 'filtros'));
    }

    /**
     * Ficha de una oferta con su empresa y sus postulaciones.
     *
     * [CRUD-READ]
     * [INYECCION-DEPENDENCIAS] Laravel inyecta la Oferta por route model binding:
     * busca el {oferta} de la URL y responde 404 si no existe.
     */
    public function show(Oferta $oferta): View
    {
        $oferta->load([
            'empresa',
            'postulaciones' => fn ($query) => $query->latest('fecha_postulacion'),
        ]);

        return view('ofertas.show', compact('oferta'));
    }

    /**
     * Normaliza los filtros del GET: texto recortado y acotado, y solo valores
     * conocidos de modalidad y tipo (cualquier otro valor se ignora).
     *
     * @return array{q: string, modalidad: string, tipo: string}
     */
    private function filtros(Request $request): array
    {
        $q = $request->query('q');
        $modalidad = $request->query('modalidad');
        $tipo = $request->query('tipo');

        return [
            'q' => is_string($q) ? trim(mb_substr(trim($q), 0, self::BUSQUEDA_MAX)) : '',
            'modalidad' => is_string($modalidad) && array_key_exists($modalidad, Oferta::MODALIDADES) ? $modalidad : '',
            'tipo' => is_string($tipo) && array_key_exists($tipo, Oferta::TIPOS) ? $tipo : '',
        ];
    }
}
