<?php

namespace Tests\Feature\Postulaciones;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PostulacionLecturaTest extends TestCase
{
    use CreaDatosDePostulaciones;
    use RefreshDatabase;

    public function test_listado_vacio_muestra_mensaje(): void
    {
        $this->get(route('postulaciones.index'))
            ->assertOk()
            ->assertViewIs('postulaciones.index')
            ->assertSee('No hay postulaciones registradas.');
    }

    public function test_listado_carga_oferta_y_empresa_sin_n_mas_1(): void
    {
        foreach (['Uno', 'Dos', 'Tres'] as $nombre) {
            $this->crearPostulacion($this->crearOferta(['titulo' => "Oferta {$nombre}"], "Empresa {$nombre}"));
        }

        DB::enableQueryLog();
        $respuesta = $this->get(route('postulaciones.index'))->assertOk();
        $consultas = count(DB::getQueryLog());

        $respuesta->assertSee('Oferta Dos')->assertSee('Empresa Tres');
        $postulacion = $respuesta->viewData('postulaciones')->first();
        $this->assertTrue($postulacion->relationLoaded('oferta'));
        $this->assertTrue($postulacion->oferta->relationLoaded('empresa'));
        // conteo + pagina + ofertas + empresas + selector de ofertas
        $this->assertLessThanOrEqual(5, $consultas);
    }

    public function test_filtra_por_oferta_y_estado_y_conserva_la_query_en_la_paginacion(): void
    {
        $laravel = $this->crearOferta(['titulo' => 'Desarrollador Laravel']);
        $datos = $this->crearOferta(['titulo' => 'Analista de Datos']);

        for ($i = 1; $i <= 12; $i++) {
            $this->crearPostulacion($laravel, ['nombre_candidato' => "Candidato Laravel {$i}", 'estado' => 'recibida']);
        }
        $this->crearPostulacion($laravel, ['nombre_candidato' => 'Aceptado Laravel', 'estado' => 'aceptada']);
        $this->crearPostulacion($datos, ['nombre_candidato' => 'Candidata Datos', 'estado' => 'recibida']);

        $respuesta = $this->get(route('postulaciones.index', ['oferta_id' => $laravel->id]))->assertOk();
        $this->assertSame(13, $respuesta->viewData('postulaciones')->total());
        $this->assertSame(['oferta_id' => $laravel->id, 'estado' => ''], $respuesta->viewData('filtros'));
        $respuesta->assertDontSee('Candidata Datos')
            ->assertSee('oferta_id='.$laravel->id.'&amp;page=2', false);

        $respuesta = $this->get(route('postulaciones.index', ['oferta_id' => $laravel->id, 'estado' => 'aceptada']));
        $this->assertSame(['Aceptado Laravel'], $respuesta->viewData('postulaciones')->pluck('nombre_candidato')->all());

        $this->get(route('postulaciones.index', ['oferta_id' => $datos->id, 'estado' => 'rechazada']))
            ->assertSee('No hay postulaciones que coincidan con los filtros.');
    }

    public function test_filtros_invalidos_se_ignoran(): void
    {
        $this->crearPostulacion($this->crearOferta());

        foreach ([['oferta_id' => 'abc'], ['oferta_id' => '999'], ['estado' => 'borrada'], ['oferta_id' => ['1']]] as $query) {
            $respuesta = $this->get(route('postulaciones.index', $query))->assertOk();
            $this->assertSame(1, $respuesta->viewData('postulaciones')->total());
        }
    }

    public function test_ficha_muestra_postulacion_oferta_y_empresa(): void
    {
        $oferta = $this->crearOferta(['titulo' => 'Pasantía QA'], 'Lempa Consultores');
        $postulacion = $this->crearPostulacion($oferta, [
            'nombre_candidato' => 'María López',
            'enlace_cv' => 'https://example.com/cv.pdf',
            'estado' => 'en_revision',
        ]);

        $this->get(route('postulaciones.show', $postulacion))
            ->assertOk()
            ->assertSee('María López')
            ->assertSee('Pasantía QA')
            ->assertSee('Lempa Consultores')
            ->assertSee('En revisión')
            ->assertSee('href="https://example.com/cv.pdf"', false)
            ->assertSee('href="'.route('postulaciones.index', ['oferta_id' => $oferta->id]).'"', false);
    }

    public function test_ficha_inexistente_responde_404(): void
    {
        $this->get('/postulaciones/999')->assertNotFound();
    }

    public function test_escapa_datos_y_no_enlaza_cv_con_esquema_peligroso(): void
    {
        $postulacion = $this->crearPostulacion($this->crearOferta(), [
            'nombre_candidato' => '<script>alert(1)</script>',
            'enlace_cv' => 'javascript:alert(1)',
        ]);

        foreach ([route('postulaciones.show', $postulacion), route('postulaciones.index')] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertDontSee('<script>alert(1)</script>', false)
                ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
                ->assertDontSee('href="javascript:', false)
                ->assertSee('Enlace no válido');
        }
    }

    public function test_la_ficha_de_oferta_sigue_mostrando_todas_sus_postulaciones(): void
    {
        $oferta = $this->crearOferta();
        $otra = $this->crearOferta(['titulo' => 'Otra oferta']);
        $this->crearPostulacion($oferta, ['nombre_candidato' => 'Primera Persona']);
        $this->crearPostulacion($oferta, ['nombre_candidato' => 'Segunda Persona']);
        $this->crearPostulacion($otra, ['nombre_candidato' => 'Persona Ajena']);

        $respuesta = $this->get(route('ofertas.show', $oferta))
            ->assertOk()
            ->assertSee('Primera Persona')
            ->assertSee('Segunda Persona')
            ->assertDontSee('Persona Ajena');

        $this->assertTrue($respuesta->viewData('oferta')->relationLoaded('postulaciones'));
        $this->assertTrue($respuesta->viewData('oferta')->relationLoaded('empresa'));
    }
}
