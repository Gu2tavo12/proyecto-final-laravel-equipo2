<?php

namespace Tests\Feature\Postulaciones;

use App\Models\Oferta;
use App\Models\Postulacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PostulacionStoreTest extends TestCase
{
    use CreaDatosDePostulaciones;
    use RefreshDatabase;

    private Oferta $oferta;

    protected function setUp(): void
    {
        parent::setUp();

        $this->oferta = $this->crearOferta(['titulo' => 'Desarrollador Laravel']);
    }

    /**
     * @return array<string, string>
     */
    private function datos(array $cambios = []): array
    {
        return array_merge([
            'oferta_id' => (string) $this->oferta->id,
            'nombre_candidato' => 'Ana Martínez',
            'correo' => 'ana@example.com',
            'carrera' => 'Ingeniería en Sistemas',
            'enlace_cv' => 'https://example.com/cv/ana.pdf',
        ], $cambios);
    }

    public function test_formulario_solo_ofrece_ofertas_activas_y_preselecciona_oferta(): void
    {
        $cerrada = $this->crearOferta(['titulo' => 'Oferta Cerrada', 'fecha_publicacion' => today()->subDays(20), 'fecha_cierre' => today()->subDay()]);

        $respuesta = $this->get(route('postulaciones.create', ['oferta_id' => $this->oferta->id]))
            ->assertOk()
            ->assertSee('name="_token"', false)
            ->assertSee('Desarrollador Laravel')
            ->assertDontSee('Oferta Cerrada');
        $this->assertSame($this->oferta->id, $respuesta->viewData('ofertaSeleccionada'));
        $respuesta->assertSee('<option value="'.$this->oferta->id.'" selected', false);

        // Una oferta cerrada o inexistente no se preselecciona
        foreach ([$cerrada->id, 999, 'x'] as $id) {
            $this->assertNull($this->get(route('postulaciones.create', ['oferta_id' => $id]))->viewData('ofertaSeleccionada'));
        }
    }

    public function test_sin_ofertas_activas_muestra_aviso(): void
    {
        $this->oferta->update(['fecha_publicacion' => today()->subDays(10), 'fecha_cierre' => today()->subDay()]);

        $this->get(route('postulaciones.create'))
            ->assertOk()
            ->assertSee('No hay ofertas activas en este momento.')
            ->assertDontSee('name="nombre_candidato"', false);
    }

    public function test_registra_postulacion_con_fecha_de_hoy_y_estado_recibida(): void
    {
        $respuesta = $this->post(route('postulaciones.store'), $this->datos([
            'correo' => '  Ana.Martinez@Example.COM ',
            // El cliente intenta fijar estado y fecha: se ignoran
            'estado' => 'aceptada',
            'fecha_postulacion' => '2020-01-01',
        ]));

        $postulacion = Postulacion::sole();
        $respuesta->assertRedirect(route('postulaciones.show', $postulacion))
            ->assertSessionHas('exito', 'Postulación registrada correctamente.');

        $this->assertSame('ana.martinez@example.com', $postulacion->correo);
        $this->assertSame('recibida', $postulacion->estado);
        $this->assertTrue($postulacion->fecha_postulacion->isSameDay(today()));
        $this->assertSame($this->oferta->id, $postulacion->oferta_id);
    }

    public function test_rechaza_correo_duplicado_en_la_misma_oferta_aunque_cambien_mayusculas(): void
    {
        $this->crearPostulacion($this->oferta, ['correo' => 'ana@example.com']);

        $this->from(route('postulaciones.create'))
            ->post(route('postulaciones.store'), $this->datos(['correo' => ' ANA@Example.com']))
            ->assertRedirect(route('postulaciones.create'))
            ->assertSessionHasErrors(['correo' => 'Este correo ya está postulado a esta oferta.'])
            ->assertSessionHasInput('nombre_candidato', 'Ana Martínez');

        $this->assertSame(1, Postulacion::count());
    }

    public function test_el_mismo_correo_puede_postularse_a_otra_oferta(): void
    {
        $otra = $this->crearOferta(['titulo' => 'Analista de Datos']);
        $this->crearPostulacion($otra, ['correo' => 'ana@example.com']);

        $this->post(route('postulaciones.store'), $this->datos())->assertSessionHasNoErrors();

        $this->assertSame(2, Postulacion::where('correo', 'ana@example.com')->count());
    }

    public function test_oferta_que_cerro_ayer_no_recibe_postulaciones(): void
    {
        $this->oferta->update(['fecha_publicacion' => today()->subDays(10), 'fecha_cierre' => today()->subDay()]);

        $this->post(route('postulaciones.store'), $this->datos())
            ->assertSessionHasErrors(['oferta_id' => 'La oferta seleccionada ya cerró y no recibe postulaciones.']);

        $this->assertSame(0, Postulacion::count());
    }

    public function test_oferta_que_cierra_hoy_si_recibe_postulaciones(): void
    {
        $this->oferta->update(['fecha_publicacion' => today()->subDays(10), 'fecha_cierre' => today()]);

        $this->post(route('postulaciones.store'), $this->datos())->assertSessionHasNoErrors();

        $this->assertSame(1, Postulacion::count());
    }

    public function test_oferta_inexistente_o_vacia(): void
    {
        $this->post(route('postulaciones.store'), $this->datos(['oferta_id' => '999']))
            ->assertSessionHasErrors(['oferta_id' => 'La oferta seleccionada no existe.']);
        $this->post(route('postulaciones.store'), $this->datos(['oferta_id' => '']))
            ->assertSessionHasErrors(['oferta_id' => 'El campo oferta es obligatorio.']);

        $this->assertSame(0, Postulacion::count());
    }

    public function test_rechaza_urls_de_cv_invalidas(): void
    {
        foreach (['no-es-url', 'javascript:alert(1)', 'ftp://example.com/cv.pdf', 'https://example.com/'.str_repeat('a', 250)] as $url) {
            $this->post(route('postulaciones.store'), $this->datos(['enlace_cv' => $url]))
                ->assertSessionHasErrors('enlace_cv');
        }

        $this->post(route('postulaciones.store'), $this->datos(['enlace_cv' => 'javascript:alert(1)']))
            ->assertSessionHasErrors(['enlace_cv' => 'El enlace del CV debe ser una URL válida que empiece con http:// o https://.']);

        $this->assertSame(0, Postulacion::count());
    }

    public function test_valida_obligatorios_y_longitudes(): void
    {
        $this->post(route('postulaciones.store'), ['oferta_id' => (string) $this->oferta->id])
            ->assertSessionHasErrors([
                'nombre_candidato' => 'El campo nombre del candidato es obligatorio.',
                'correo' => 'El campo correo es obligatorio.',
                'carrera' => 'El campo carrera es obligatorio.',
                'enlace_cv' => 'El campo enlace del CV es obligatorio.',
            ]);

        $this->post(route('postulaciones.store'), $this->datos([
            'nombre_candidato' => str_repeat('a', 151),
            'correo' => str_repeat('a', 140).'@example.com',
            'carrera' => str_repeat('c', 101),
        ]))->assertSessionHasErrors([
            'nombre_candidato' => 'El nombre no puede superar los 150 caracteres.',
            'correo' => 'El correo no puede superar los 150 caracteres.',
            'carrera' => 'La carrera no puede superar los 100 caracteres.',
        ]);

        // Los limites exactos si se aceptan
        $this->post(route('postulaciones.store'), $this->datos([
            'nombre_candidato' => str_repeat('a', 150),
            'carrera' => str_repeat('c', 100),
        ]))->assertSessionHasNoErrors();

        $this->post(route('postulaciones.store'), $this->datos(['correo' => 'correo-invalido']))
            ->assertSessionHasErrors(['correo' => 'Ingresa un correo electrónico válido.']);
    }

    public function test_conflicto_de_la_restriccion_compuesta_se_traduce_a_error_en_correo(): void
    {
        // Simula una carrera: otra peticion inserta el mismo correo despues de
        // la validacion y justo antes del INSERT de esta peticion.
        Event::listen('eloquent.creating: '.Postulacion::class, function (Postulacion $nueva) {
            Postulacion::withoutEvents(fn () => Postulacion::factory()->paraOferta($this->oferta)->create(['correo' => $nueva->correo]));
        });

        $this->from(route('postulaciones.create'))
            ->post(route('postulaciones.store'), $this->datos())
            ->assertRedirect(route('postulaciones.create'))
            ->assertSessionHasErrors(['correo' => 'Este correo ya está postulado a esta oferta.']);

        $this->assertSame(1, Postulacion::count());
    }
}
