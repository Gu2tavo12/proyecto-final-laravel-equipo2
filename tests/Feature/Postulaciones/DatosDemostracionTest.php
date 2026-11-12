<?php

namespace Tests\Feature\Postulaciones;

use App\Models\Empresa;
use App\Models\Oferta;
use App\Models\Postulacion;
use Database\Seeders\DemostracionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifica que migrate:fresh --seed deje los escenarios de la demostracion.
 */
class DatosDemostracionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Se siembra dentro de la transaccion del test (no con $seed = true),
        // para que los datos no queden en la BD de los demas tests.
        $this->seed();
    }

    public function test_cantidades_minimas_y_totales_verificables(): void
    {
        $this->assertGreaterThanOrEqual(3, Empresa::count());
        $this->assertSame(20, Oferta::count());
        $this->assertSame(40, Postulacion::count());

        foreach (array_keys(Postulacion::ESTADOS) as $estado) {
            $this->assertSame(10, Postulacion::where('estado', $estado)->count(), "estado {$estado}");
        }
    }

    public function test_ranking_de_postulaciones_sin_empates_en_los_primeros_lugares(): void
    {
        $ranking = Oferta::withCount('postulaciones')
            ->orderByDesc('postulaciones_count')
            ->take(6)
            ->pluck('postulaciones_count', 'titulo')
            ->all();

        $this->assertSame([
            'Desarrollador Laravel Senior' => 7,
            'Científico de Datos Junior' => 6,
            'Ingeniero de Infraestructura Cloud' => 5,
            'Pasantía en Desarrollo Web' => 4,
            'Técnico de Soporte en Sitio' => 3,
            'Auxiliar Administrativo' => 2,
        ], $ranking);
    }

    public function test_escenarios_de_reglas_de_negocio(): void
    {
        // Una oferta activa por modalidad
        foreach (array_keys(Oferta::MODALIDADES) as $modalidad) {
            $this->assertTrue(Oferta::activas()->where('modalidad', $modalidad)->exists(), $modalidad);
        }

        $this->assertTrue(Oferta::where('titulo', 'Técnico de Soporte en Sitio')->sole()->estaActiva());
        $this->assertFalse(Oferta::where('titulo', 'Auxiliar Administrativo')->sole()->estaActiva());

        $pasantia = Oferta::where('titulo', 'Pasantía en Desarrollo Web')->sole();
        $this->assertSame('pasantia', $pasantia->tipo);
        $this->assertSame('A convenir', $pasantia->rango_salarial);

        $sinPostulaciones = Oferta::doesntHave('postulaciones')->pluck('titulo')->all();
        $this->assertSame(['Pasantía en Diseño UX/UI'], $sinPostulaciones);

        $this->assertSame(
            'Desarrollador Laravel Senior',
            Postulacion::where('correo', DemostracionSeeder::CORREO_DEMO)->sole()->oferta->titulo,
        );
    }

    public function test_fechas_coherentes_y_unicidad_compuesta(): void
    {
        foreach (Oferta::all() as $oferta) {
            $this->assertTrue($oferta->fecha_cierre->gt($oferta->fecha_publicacion), "oferta {$oferta->id}");
        }

        foreach (Postulacion::with('oferta')->get() as $postulacion) {
            $oferta = $postulacion->oferta;
            $this->assertTrue($postulacion->fecha_postulacion->gte($oferta->fecha_publicacion), "postulacion {$postulacion->id}");
            $this->assertTrue($postulacion->fecha_postulacion->lte($oferta->fecha_cierre), "postulacion {$postulacion->id}");
            $this->assertTrue($postulacion->fecha_postulacion->lte(today()), "postulacion {$postulacion->id}");
        }

        $duplicados = Postulacion::selectRaw('oferta_id, lower(correo) as correo_normalizado, count(*) as total')
            ->groupBy('oferta_id', 'correo_normalizado')
            ->havingRaw('count(*) > 1')
            ->count();

        $this->assertSame(0, $duplicados);
    }
}
