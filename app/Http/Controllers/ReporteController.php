<?php

namespace App\Http\Controllers;

use App\Http\ReportePdf;
use App\Models\Empresa;
use App\Models\Oferta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteController extends Controller
{
    public function index(): View
    {
        $activasPorModalidad = $this->activasPorModalidad();
        $ofertas = $this->ofertas()
            ->paginate(15, pageName: 'pagina_ofertas')
            ->withQueryString();
        $ranking = $this->ranking()
            ->paginate(15, pageName: 'pagina_empresas')
            ->withQueryString();

        return view('reporte.index', compact('activasPorModalidad', 'ofertas', 'ranking'));
    }

    public function pdf(): StreamedResponse
    {
        $activasPorModalidad = $this->activasPorModalidad();
        $modalidades = [];

        foreach (Oferta::MODALIDADES as $valor => $etiqueta) {
            $modalidades[] = [$etiqueta, (int) ($activasPorModalidad[$valor] ?? 0)];
        }

        $ofertas = $this->ofertas()->get()->map(fn (Oferta $oferta): array => [
            $oferta->titulo.' | '.$oferta->empresa->nombre,
            (int) $oferta->postulaciones_count,
        ]);
        $ranking = $this->ranking()->get()->map(fn (Empresa $empresa, int $indice): array => [
            ($indice + 1).'. '.$empresa->nombre,
            (int) $empresa->vacantes_activas,
        ]);

        $contenido = (new ReportePdf)->generar([
            ['Ofertas activas por modalidad', 'Ofertas', $modalidades],
            ['Postulaciones por oferta', 'Postulaciones', $ofertas, 'No hay ofertas registradas.'],
            ['Ranking de empresas por vacantes activas', 'Vacantes', $ranking, 'No hay empresas con ofertas activas.'],
        ]);

        return response()->streamDownload(static function () use ($contenido): void {
            echo $contenido;
        }, 'reporte-caso-7.pdf', ['Content-Type' => 'application/pdf']);
    }

    /** @return Collection<string, int> */
    private function activasPorModalidad(): Collection
    {
        return Oferta::activas()
            ->selectRaw('modalidad, count(*) as total')
            ->groupBy('modalidad')
            ->pluck('total', 'modalidad');
    }

    /** @return Builder<Oferta> */
    private function ofertas(): Builder
    {
        return Oferta::select('id', 'empresa_id', 'titulo')
            ->with('empresa:id,nombre')
            ->withCount('postulaciones')
            ->orderByDesc('postulaciones_count')
            ->orderBy('id');
    }

    /** @return Builder<Empresa> */
    private function ranking(): Builder
    {
        $ofertasActivas = fn (Builder $ofertas): Builder => $ofertas->activas();

        return Empresa::select('id', 'nombre')
            ->whereHas('ofertas', $ofertasActivas)
            ->withSum(['ofertas as vacantes_activas' => $ofertasActivas], 'vacantes')
            ->orderByDesc('vacantes_activas')
            ->orderBy('nombre')
            ->orderBy('id');
    }
}
