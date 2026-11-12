<?php

namespace Tests\Feature\Ofertas;

use App\Models\Empresa;
use App\Models\Oferta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class OfertaUpdateTest extends TestCase
{
    use CreaDatosDeOfertas;
    use RefreshDatabase;

    private Empresa $empresa;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->empresa = $this->crearEmpresa();
    }

    /**
     * Oferta con una imagen ya guardada en el disco falso.
     */
    private function ofertaConImagen(): Oferta
    {
        $ruta = UploadedFile::fake()->image('actual.jpg')->store('ofertas', 'public');

        return $this->crearOferta($this->empresa, ['imagen' => $ruta]);
    }

    public function test_el_formulario_recibe_la_oferta_y_las_empresas_ordenadas(): void
    {
        $this->crearEmpresa('Beta Bancos');
        $oferta = $this->ofertaConImagen();

        $respuesta = $this->get("/ofertas/{$oferta->id}/edit")->assertOk()->assertViewIs('ofertas.edit');

        $this->assertTrue($respuesta->viewData('oferta')->is($oferta));
        $this->assertSame(['Acme Software', 'Beta Bancos'], $respuesta->viewData('empresas')->pluck('nombre')->all());
        $respuesta->assertSee('name="_method" value="PUT"', false)
            ->assertSee(e($oferta->titulo))
            ->assertSee('Imagen actual cargada');
    }

    public function test_editar_una_oferta_inexistente_da_404(): void
    {
        $this->get('/ofertas/9999/edit')->assertNotFound();
        $this->put('/ofertas/9999', $this->datosFormulario($this->empresa))->assertNotFound();
    }

    public function test_actualiza_datos_y_conserva_la_imagen_si_no_se_adjunta_otra(): void
    {
        $oferta = $this->ofertaConImagen();
        $imagen = $oferta->imagen;

        $this->put("/ofertas/{$oferta->id}", $this->datosFormulario($this->empresa, ['titulo' => 'Título nuevo']))
            ->assertRedirect(route('ofertas.show', $oferta))
            ->assertSessionHas('exito', 'Oferta actualizada correctamente.');

        $oferta->refresh();
        $this->assertSame('Título nuevo', $oferta->titulo);
        $this->assertSame($imagen, $oferta->imagen);
        Storage::disk('public')->assertExists($imagen);
    }

    public function test_reemplaza_la_imagen_y_borra_la_anterior(): void
    {
        $oferta = $this->ofertaConImagen();
        $anterior = $oferta->imagen;

        $this->put("/ofertas/{$oferta->id}", $this->datosFormulario($this->empresa, [
            'imagen' => UploadedFile::fake()->image('nueva.webp'),
        ]))->assertSessionHasNoErrors();

        $oferta->refresh();
        $this->assertNotSame($anterior, $oferta->imagen);
        $this->assertStringStartsWith('ofertas/', $oferta->imagen);
        Storage::disk('public')->assertExists($oferta->imagen);
        Storage::disk('public')->assertMissing($anterior);
        $this->assertCount(1, Storage::disk('public')->allFiles());
    }

    public function test_agrega_imagen_a_una_oferta_que_no_tenia(): void
    {
        $oferta = $this->crearOferta($this->empresa, ['imagen' => null]);

        $this->put("/ofertas/{$oferta->id}", $this->datosFormulario($this->empresa, [
            'imagen' => UploadedFile::fake()->image('nueva.png'),
        ]))->assertSessionHasNoErrors();

        Storage::disk('public')->assertExists($oferta->refresh()->imagen);
    }

    public function test_valida_igual_que_al_crear_y_no_toca_la_imagen(): void
    {
        $oferta = $this->ofertaConImagen();
        $original = $oferta->only(['titulo', 'imagen', 'vacantes']);

        $this->from("/ofertas/{$oferta->id}/edit")
            ->put("/ofertas/{$oferta->id}", $this->datosFormulario($this->empresa, [
                'vacantes' => '0',
                'fecha_cierre' => '2000-01-01',
                'imagen' => UploadedFile::fake()->image('animacion.gif'),
            ]))
            ->assertRedirect("/ofertas/{$oferta->id}/edit")
            ->assertSessionHasErrors(['vacantes', 'fecha_cierre', 'imagen']);

        $this->assertSame($original, $oferta->refresh()->only(['titulo', 'imagen', 'vacantes']));
        $this->assertSame([$original['imagen']], Storage::disk('public')->allFiles());
    }

    public function test_empleo_exige_salarios_tambien_al_editar(): void
    {
        $oferta = $this->crearOferta($this->empresa);

        $this->put("/ofertas/{$oferta->id}", $this->datosFormulario($this->empresa, [
            'tipo' => 'empleo',
            'salario_minimo' => '',
            'salario_maximo' => '',
        ]))->assertSessionHasErrors(['salario_minimo', 'salario_maximo']);
    }

    public function test_si_falla_la_bd_borra_la_nueva_y_conserva_la_vigente(): void
    {
        $oferta = $this->ofertaConImagen();
        $vigente = $oferta->imagen;
        $tituloOriginal = $oferta->titulo;
        Oferta::updating(fn () => throw new RuntimeException('Fallo simulado de la BD'));

        $this->from("/ofertas/{$oferta->id}/edit")
            ->put("/ofertas/{$oferta->id}", $this->datosFormulario($this->empresa, [
                'titulo' => 'No debe guardarse',
                'imagen' => UploadedFile::fake()->image('nueva.png'),
            ]))
            ->assertRedirect("/ofertas/{$oferta->id}/edit")
            ->assertSessionHas('error');

        $oferta->refresh();
        $this->assertSame($tituloOriginal, $oferta->titulo);
        $this->assertSame($vigente, $oferta->imagen);
        $this->assertSame([$vigente], Storage::disk('public')->allFiles());
    }
}
