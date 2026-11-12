<?php

namespace Tests\Feature;

use const Dom\HTML_NO_DEFAULT_NS;

use App\Models\Postulacion;
use Dom\HTMLDocument;
use Dom\XPath;
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

        $sinPostulaciones = $this->crearOferta($lider, [
            'titulo' => 'Oferta sin postulaciones',
            'modalidad' => 'presencial',
            'vacantes' => 3,
        ]);
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
            ->assertSee(route('reporte.pdf'))
            ->assertSee('Suma de vacantes de las ofertas activas.')
            ->assertSee(route('ofertas.show', $sinPostulaciones))
            ->assertSee(route('ofertas.show', $cerrada))
            ->assertSee(route('empresas.show', $lider))
            ->assertDontSee(route('empresas.show', $inactiva));

        $modalidades = $respuesta->viewData('activasPorModalidad');
        $this->assertSame(2, $modalidades['presencial']);
        $this->assertSame(2, $modalidades['remota']);
        $this->assertFalse($modalidades->has('hibrida'));

        $conteos = $respuesta->viewData('ofertas')->pluck('postulaciones_count', 'id');
        $this->assertCount(6, $conteos);
        $this->assertSame(2, $conteos[$cierraHoy->id]);
        $this->assertSame(1, $conteos[$cerrada->id]);
        $this->assertSame(0, $conteos[$sinPostulaciones->id]);

        $ranking = $respuesta->viewData('ranking');
        $this->assertSame([$lider->id, $alfa->id, $zeta->id], $ranking->pluck('id')->all());
        $this->assertSame([7, 5, 5], $ranking->pluck('vacantes_activas')->all());

        $pantalla = new XPath(HTMLDocument::createFromString($respuesta->getContent(), LIBXML_NOERROR | HTML_NO_DEFAULT_NS));

        foreach (['Presencial' => 2, 'Remota' => 2, 'Híbrida' => 0] as $modalidad => $total) {
            $this->assertSame((string) $total, $pantalla->evaluate(
                "normalize-space(//section[@aria-labelledby='titulo-modalidades']//dl[dt='$modalidad']/dd)"
            ));
        }

        foreach ([[$cierraHoy, 2], [$cerrada, 1], [$sinPostulaciones, 0]] as [$oferta, $total]) {
            $enlace = route('ofertas.show', $oferta);
            $this->assertSame((string) $total, $pantalla->evaluate(
                "normalize-space(//table[@aria-labelledby='titulo-postulaciones']//tr[th/a/@href='$enlace']/td)"
            ));
        }

        $filasRanking = $pantalla->query("//table[@aria-labelledby='titulo-ranking']/tbody/tr");
        $this->assertCount(3, $filasRanking);

        foreach ([['Líder', '7'], ['Alfa', '5'], ['Zeta', '5']] as $indice => [$empresa, $vacantes]) {
            $this->assertSame($empresa, $pantalla->evaluate('normalize-space(th/a)', $filasRanking[$indice]));
            $this->assertSame($vacantes, $pantalla->evaluate('normalize-space(td[last()])', $filasRanking[$indice]));
        }

        $pdf = $this->get(route('reporte.pdf'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertDownload('reporte-caso-7.pdf')
            ->streamedContent();

        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertStringContainsString('(H'.chr(237).'brida) Tj', $pdf);
        $this->assertStringContainsString('(Oferta cerrada | Inactiva) Tj', $pdf);
        $this->assertStringContainsString('(1. L'.chr(237).'der) Tj', $pdf);
        $this->assertStringContainsString('(2. Alfa) Tj', $pdf);
        $this->assertStringContainsString('(3. Zeta) Tj', $pdf);
        $this->assertStringNotContainsString('(4. Inactiva) Tj', $pdf);
    }

    public function test_muestra_estados_vacios_y_las_tres_modalidades_en_cero(): void
    {
        $this->get(route('reporte.index'))
            ->assertOk()
            ->assertSeeTextInOrder(['Presencial', '0', 'Remota', '0', 'Híbrida', '0'])
            ->assertSee('No hay ofertas activas.')
            ->assertSee('No hay ofertas registradas.')
            ->assertSee('No hay empresas con ofertas activas.');

        $pdf = $this->get(route('reporte.pdf'))->assertOk()->streamedContent();

        $this->assertStringContainsString('(No hay ofertas registradas.) Tj', $pdf);
        $this->assertStringContainsString('(No hay empresas con ofertas activas.) Tj', $pdf);
    }

    public function test_exporta_todas_las_ofertas_aunque_el_reporte_web_este_paginado(): void
    {
        $empresa = $this->crearEmpresa();

        for ($numero = 1; $numero <= 50; $numero++) {
            $this->crearOferta($empresa, ['titulo' => "Oferta para exportar ($numero)"]);
        }

        $pdf = $this->get(route('reporte.pdf', ['pagina_ofertas' => 2]))
            ->assertOk()
            ->streamedContent();

        $this->assertSame(50, substr_count($pdf, 'Oferta para exportar'));
        $this->assertStringContainsString('Oferta para exportar \\(50\\)', $pdf);
        $this->assertGreaterThan(1, substr_count($pdf, '/Type /Page '));
    }
}
