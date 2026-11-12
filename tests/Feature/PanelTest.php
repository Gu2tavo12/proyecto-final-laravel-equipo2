<?php

namespace Tests\Feature;

use App\Models\Oferta;
use App\Models\Postulacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Ofertas\CreaDatosDeOfertas;
use Tests\TestCase;

class PanelTest extends TestCase
{
    use CreaDatosDeOfertas, RefreshDatabase;

    public function test_muestra_los_accesos_y_los_indicadores_con_datos(): void
    {
        $empresa = $this->crearEmpresa();
        $activa = $this->crearOferta($empresa, ['modalidad' => 'remota', 'tipo' => 'empleo']);
        Oferta::factory()->cerrada()->create([
            'empresa_id' => $empresa->id,
            'modalidad' => 'presencial',
            'tipo' => 'pasantia',
        ]);
        Postulacion::factory()->paraOferta($activa)->create();

        $this->get(route('panel'))
            ->assertOk()
            ->assertSee(route('ofertas.index'))
            ->assertSee(route('empresas.index'))
            ->assertSee(route('postulaciones.index'))
            ->assertSee(route('reporte.index'))
            ->assertViewHasAll([
                'totalOfertas' => 2,
                'totalEmpresas' => 1,
                'totalPostulaciones' => 1,
                'ofertasActivas' => 1,
                'ofertasCerradas' => 1,
            ])
            ->assertViewHas('porModalidad', fn ($conteos) => $conteos['remota'] === 1 && $conteos['presencial'] === 1)
            ->assertViewHas('porTipo', fn ($conteos) => $conteos['empleo'] === 1 && $conteos['pasantia'] === 1)
            ->assertSeeText('Activa')
            ->assertSeeText('Cerrada');
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
