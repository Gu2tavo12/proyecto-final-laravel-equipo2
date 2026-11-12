<?php

namespace Tests\Feature;

use App\Models\Postulacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Ofertas\CreaDatosDeOfertas;
use Tests\TestCase;

class ReporteTest extends TestCase
{
    use CreaDatosDeOfertas, RefreshDatabase;

    public function test_calcula_los_indicadores_y_enlaza_las_fichas(): void
    {
        $zeta = $this->crearEmpresa('Zeta');
        $alfa = $this->crearEmpresa('Alfa');
        $lider = $this->crearEmpresa('Líder');
        $inactiva = $this->crearEmpresa('Inactiva');
        $this->crearEmpresa('Sin ofertas');

        $cierraHoy = $this->crearOferta($lider, [
            'titulo' => 'Oferta que cierra hoy',
            'modalidad' => 'presencial',
            'fecha_publicacion' => today()->subWeek(),
            'vacantes' => 4,
        ]);
        DB::table('ofertas')->where('id', $cierraHoy->id)->update(['fecha_cierre' => today()->toDateString()]);

        $sinPostulaciones = $this->crearOferta($lider, ['modalidad' => 'presencial', 'vacantes' => 3]);
        $this->crearOferta($zeta, ['modalidad' => 'remota', 'vacantes' => 5]);
        $this->crearOferta($alfa, ['modalidad' => 'remota', 'vacantes' => 5]);
        $this->crearOferta($zeta, [
            'modalidad' => 'hibrida',
            'vacantes' => 50,
            'fecha_publicacion' => today()->subMonth(),
            'fecha_cierre' => today()->subDay(),
        ]);
        $cerrada = $this->crearOferta($inactiva, [
            'titulo' => 'Oferta cerrada',
            'modalidad' => 'hibrida',
            'vacantes' => 100,
            'fecha_publicacion' => today()->subMonth(),
            'fecha_cierre' => today()->subDay(),
        ]);

        Postulacion::factory()->count(2)->paraOferta($cierraHoy->refresh())->create();
        Postulacion::factory()->paraOferta($cerrada)->create();

        $respuesta = $this->get(route('reporte.index'))
            ->assertOk()
            ->assertSee('Suma de vacantes de las ofertas activas.')
            ->assertSee(route('ofertas.show', $sinPostulaciones))
            ->assertSee(route('ofertas.show', $cerrada))
            ->assertSee(route('empresas.show', $lider))
            ->assertDontSee(route('empresas.show', $inactiva));

        $modalidades = $respuesta->viewData('activasPorModalidad');
        $this->assertSame(2, $modalidades['presencial']);
        $this->assertSame(2, $modalidades['remota']);
        $this->assertFalse($modalidades->has('hibrida'));
        $respuesta->assertSeeTextInOrder(['Híbrida', '0']);

        $conteos = $respuesta->viewData('ofertas')->pluck('postulaciones_count', 'id');
        $this->assertCount(6, $conteos);
        $this->assertSame(2, $conteos[$cierraHoy->id]);
        $this->assertSame(1, $conteos[$cerrada->id]);
        $this->assertSame(0, $conteos[$sinPostulaciones->id]);

        $ranking = $respuesta->viewData('ranking');
        $this->assertSame([$lider->id, $alfa->id, $zeta->id], $ranking->pluck('id')->all());
        $this->assertSame([7, 5, 5], $ranking->pluck('vacantes_activas')->all());
    }

    public function test_muestra_estados_vacios_y_las_tres_modalidades_en_cero(): void
    {
        $this->get(route('reporte.index'))
            ->assertOk()
            ->assertSeeTextInOrder(['Presencial', '0', 'Remota', '0', 'Híbrida', '0'])
            ->assertSee('No hay ofertas activas.')
            ->assertSee('No hay ofertas registradas.')
            ->assertSee('No hay empresas con ofertas activas.');
    }
}
