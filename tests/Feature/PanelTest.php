<?php

namespace Tests\Feature;

use App\Models\Postulacion;
use Dom\HTMLDocument;
use Dom\XPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Ofertas\CreaDatosDeOfertas;
use Tests\TestCase;

use const Dom\HTML_NO_DEFAULT_NS;

class PanelTest extends TestCase
{
    use CreaDatosDeOfertas, RefreshDatabase;

    public function test_muestra_los_accesos_y_los_indicadores_con_datos(): void
    {
        $empresa = $this->crearEmpresa();
        $activa = $this->crearOferta($empresa, ['modalidad' => 'remota', 'tipo' => 'empleo']);
        $this->crearOferta($empresa, ['modalidad' => 'remota', 'tipo' => 'pasantia']);
        $this->crearOferta($empresa, ['modalidad' => 'hibrida', 'tipo' => 'empleo']);
        $this->crearOferta($empresa, [
            'modalidad' => 'presencial',
            'tipo' => 'pasantia',
            'fecha_publicacion' => today()->subMonth(),
            'fecha_cierre' => today()->subDay(),
        ]);
        Postulacion::factory()->count(2)->paraOferta($activa)->create();

        $respuesta = $this->get(route('panel'))
            ->assertOk()
            ->assertViewHasAll([
                'totalOfertas' => 4,
                'totalEmpresas' => 1,
                'totalPostulaciones' => 2,
                'ofertasActivas' => 3,
                'ofertasCerradas' => 1,
            ])
            ->assertSeeText('Activa')
            ->assertSeeText('Cerrada');

        $pantalla = new XPath(HTMLDocument::createFromString($respuesta->getContent(), LIBXML_NOERROR | HTML_NO_DEFAULT_NS));

        foreach (['Ofertas' => 4, 'Empresas' => 1, 'Postulaciones' => 2, 'Ofertas activas' => 3, 'Ofertas cerradas' => 1] as $indicador => $total) {
            $this->assertSame((string) $total, $pantalla->evaluate(
                "normalize-space(//main//p[normalize-space(.)='$indicador']/following-sibling::p[1])"
            ));
        }

        foreach (['Presencial' => 1, 'Remota' => 2, 'Híbrida' => 1, 'Empleo' => 2, 'Pasantía' => 2] as $categoria => $total) {
            $this->assertSame((string) $total, $pantalla->evaluate(
                "normalize-space(//main//tr[td[1][normalize-space(.)='$categoria']]/td[2])"
            ));
        }

        foreach (['Ofertas' => 'ofertas.index', 'Empresas' => 'empresas.index', 'Postulaciones' => 'postulaciones.index', 'Reporte' => 'reporte.index'] as $modulo => $ruta) {
            $enlace = $pantalla->evaluate("string(//main//nav//a[normalize-space(.)='$modulo']/@href)");
            $this->assertSame(route($ruta), $enlace);
            $this->get($enlace)->assertOk()->assertViewIs($ruta);
        }
    }

    public function test_muestra_los_accesos_y_los_indicadores_en_cero_sin_registros(): void
    {
        $this->get(route('panel'))
            ->assertOk()
            ->assertSee(route('ofertas.index'))
            ->assertSee(route('empresas.index'))
            ->assertSee(route('postulaciones.index'))
            ->assertSee(route('reporte.index'))
            ->assertViewHasAll([
                'totalOfertas' => 0,
                'totalEmpresas' => 0,
                'totalPostulaciones' => 0,
                'ofertasActivas' => 0,
                'ofertasCerradas' => 0,
            ])
            ->assertSee('No hay ofertas registradas.');
    }
}
