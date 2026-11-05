<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Oferta;
use App\Models\Postulacion;
use Illuminate\View\View;

/**
 * Panel principal con indicadores basicos del sistema.
 */
class PanelController extends Controller
{
    /**
     * Indicadores del panel: totales, ofertas activas/cerradas,
     * conteos por modalidad y tipo, y las 5 ofertas mas recientes.
     *
     * [CRUD-READ] Solo consultas Eloquent (sin SQL crudo concatenado).
     */
    public function index(): View
    {
        $totalOfertas = Oferta::count();
        $totalEmpresas = Empresa::count();
        $totalPostulaciones = Postulacion::count();

        // Scope local activas(): fecha de cierre hoy o posterior.
        $ofertasActivas = Oferta::activas()->count();
        $ofertasCerradas = $totalOfertas - $ofertasActivas;

        // Conteos agrupados: [valor en BD => cantidad].
        $porModalidad = Oferta::selectRaw('modalidad, count(*) as total')
            ->groupBy('modalidad')
            ->pluck('total', 'modalidad');

        $porTipo = Oferta::selectRaw('tipo, count(*) as total')
            ->groupBy('tipo')
            ->pluck('total', 'tipo');

        // with('empresa') carga las empresas en una sola consulta (evita N+1).
        $ofertasRecientes = Oferta::with('empresa')
            ->latest('fecha_publicacion')
            ->take(5)
            ->get();

        return view('panel.index', compact(
            'totalOfertas',
            'totalEmpresas',
            'totalPostulaciones',
            'ofertasActivas',
            'ofertasCerradas',
            'porModalidad',
            'porTipo',
            'ofertasRecientes',
        ));
    }
}
