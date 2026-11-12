<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Oferta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class ReporteController extends Controller
{
    public function index(): View
    {
        $activasPorModalidad = Oferta::activas()
            ->selectRaw('modalidad, count(*) as total')
            ->groupBy('modalidad')
            ->pluck('total', 'modalidad');

        $ofertas = Oferta::select('id', 'empresa_id', 'titulo')
            ->with('empresa:id,nombre')
            ->withCount('postulaciones')
            ->orderByDesc('postulaciones_count')
            ->orderBy('id')
            ->paginate(15, pageName: 'pagina_ofertas')
            ->withQueryString();

        $ofertasActivas = fn (Builder $ofertas): Builder => $ofertas->activas();

        $ranking = Empresa::select('id', 'nombre')
            ->whereHas('ofertas', $ofertasActivas)
            ->withSum(['ofertas as vacantes_activas' => $ofertasActivas], 'vacantes')
            ->orderByDesc('vacantes_activas')
            ->orderBy('nombre')
            ->orderBy('id')
            ->paginate(15, pageName: 'pagina_empresas')
            ->withQueryString();

        return view('reporte.index', compact('activasPorModalidad', 'ofertas', 'ranking'));
    }
}
