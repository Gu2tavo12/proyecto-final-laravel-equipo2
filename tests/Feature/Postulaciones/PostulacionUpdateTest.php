<?php

namespace Tests\Feature\Postulaciones;

use App\Models\Oferta;
use App\Models\Postulacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostulacionUpdateTest extends TestCase
{
    use CreaDatosDePostulaciones;
    use RefreshDatabase;

    private Oferta $oferta;

    private Postulacion $postulacion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->oferta = $this->crearOferta(['titulo' => 'Desarrollador Laravel']);
        $this->postulacion = $this->crearPostulacion($this->oferta, [
            'correo' => 'ana@example.com',
            'estado' => 'recibida',
            'fecha_postulacion' => today()->subDays(3),
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function datos(array $cambios = []): array
    {
        return array_merge([
            'nombre_candidato' => 'Ana Martínez',
            'correo' => 'ana@example.com',
            'carrera' => 'Ingeniería en Sistemas',
            'enlace_cv' => 'https://example.com/cv/ana.pdf',
            'estado' => 'en_revision',
        ], $cambios);
    }

    public function test_formulario_de_edicion_precarga_datos_con_put_y_csrf(): void
    {
        $this->get(route('postulaciones.edit', $this->postulacion))
            ->assertOk()
            ->assertViewIs('postulaciones.edit')
            ->assertSee('name="_token"', false)
            ->assertSee('name="_method" value="PUT"', false)
            ->assertSee('value="ana@example.com"', false)
            ->assertSee('<option value="recibida" selected', false);
    }

    public function test_cambia_estado_y_conserva_oferta_y_fecha(): void
    {
        $otra = $this->crearOferta(['titulo' => 'Otra']);
        $fechaOriginal = $this->postulacion->fecha_postulacion->toDateString();

        $this->put(route('postulaciones.update', $this->postulacion), $this->datos([
            'estado' => 'aceptada',
            'correo' => ' ANA@example.com ',
            // Intentos de mover la postulacion o cambiar su fecha: se ignoran
            'oferta_id' => (string) $otra->id,
            'fecha_postulacion' => '2020-01-01',
        ]))
            ->assertRedirect(route('postulaciones.show', $this->postulacion))
            ->assertSessionHas('exito', 'Postulación actualizada correctamente.');

        $this->postulacion->refresh();
        $this->assertSame('aceptada', $this->postulacion->estado);
        $this->assertSame('ana@example.com', $this->postulacion->correo);
        $this->assertSame($this->oferta->id, $this->postulacion->oferta_id);
        $this->assertSame($fechaOriginal, $this->postulacion->fecha_postulacion->toDateString());
    }

    public function test_rechaza_estado_desconocido(): void
    {
        $this->put(route('postulaciones.update', $this->postulacion), $this->datos(['estado' => 'contratada']))
            ->assertSessionHasErrors(['estado' => 'El estado seleccionado no es válido.']);

        $this->assertSame('recibida', $this->postulacion->fresh()->estado);
    }

    public function test_rechaza_correo_usado_por_otra_postulacion_de_la_misma_oferta(): void
    {
        $this->crearPostulacion($this->oferta, ['correo' => 'luis@example.com']);

        $this->from(route('postulaciones.edit', $this->postulacion))
            ->put(route('postulaciones.update', $this->postulacion), $this->datos(['correo' => 'Luis@Example.com']))
            ->assertRedirect(route('postulaciones.edit', $this->postulacion))
            ->assertSessionHasErrors(['correo' => 'Este correo ya está postulado a esta oferta.']);

        $this->assertSame('ana@example.com', $this->postulacion->fresh()->correo);
    }

    public function test_acepta_correo_usado_en_otra_oferta(): void
    {
        $this->crearPostulacion($this->crearOferta(['titulo' => 'Otra']), ['correo' => 'luis@example.com']);

        $this->put(route('postulaciones.update', $this->postulacion), $this->datos(['correo' => 'luis@example.com']))
            ->assertSessionHasNoErrors();

        $this->assertSame('luis@example.com', $this->postulacion->fresh()->correo);
    }

    public function test_gestiona_postulacion_historica_de_oferta_cerrada(): void
    {
        $this->oferta->update(['fecha_publicacion' => today()->subDays(30), 'fecha_cierre' => today()->subDays(5)]);

        $this->get(route('postulaciones.edit', $this->postulacion))->assertOk()->assertSee('Cerrada');

        $this->put(route('postulaciones.update', $this->postulacion), $this->datos(['estado' => 'rechazada']))
            ->assertSessionHasNoErrors();

        $this->assertSame('rechazada', $this->postulacion->fresh()->estado);
    }

    public function test_valida_campos_del_candidato(): void
    {
        $this->put(route('postulaciones.update', $this->postulacion), $this->datos([
            'nombre_candidato' => '',
            'enlace_cv' => 'javascript:alert(1)',
        ]))->assertSessionHasErrors(['nombre_candidato', 'enlace_cv']);

        $this->assertSame('recibida', $this->postulacion->fresh()->estado);
    }

    public function test_postulacion_inexistente_responde_404(): void
    {
        $this->get('/postulaciones/999/edit')->assertNotFound();
        $this->put('/postulaciones/999', $this->datos())->assertNotFound();
    }
}
