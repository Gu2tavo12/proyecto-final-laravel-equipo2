<?php

namespace App\Http\Controllers;

use App\Models\Oferta;
use Illuminate\View\View;

/**
 * Consulta de ofertas (Fase 1): listado paginado y ficha.
 * create, store, edit, update y destroy se implementan en la Fase 2.
 */
class OfertaController extends Controller
{
    /**
     * Listado paginado de ofertas, las mas recientes primero.
     *
     * [CRUD-READ] with('empresa') carga las empresas en una sola consulta (evita N+1).
     */
    public function index(): View
    {
        $ofertas = Oferta::with('empresa')
            ->latest('fecha_publicacion')
            ->paginate(10);

        return view('ofertas.index', compact('ofertas'));
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
}
