<?php

namespace Tests\Feature\Ofertas;

use App\Models\Empresa;
use App\Models\Oferta;
use App\Models\Postulacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class OfertaDestroyTest extends TestCase
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

    private function ofertaConImagen(): Oferta
    {
        $ruta = UploadedFile::fake()->image('actual.jpg')->store('ofertas', 'public');

        return $this->crearOferta($this->empresa, ['imagen' => $ruta]);
    }

    public function test_elimina_la_oferta_su_imagen_y_sus_postulaciones(): void
    {
        $oferta = $this->ofertaConImagen();
        Postulacion::factory()->count(3)->create(['oferta_id' => $oferta->id]);
        $otra = $this->crearOferta($this->empresa);
        Postulacion::factory()->create(['oferta_id' => $otra->id]);

        $this->delete("/ofertas/{$oferta->id}")
            ->assertRedirect(route('ofertas.index'))
            ->assertSessionHas('exito', 'Oferta eliminada correctamente.');

        $this->assertModelMissing($oferta);
        Storage::disk('public')->assertMissing($oferta->imagen);
        // Cascada: solo se borran las postulaciones de la oferta eliminada.
        $this->assertSame(0, Postulacion::where('oferta_id', $oferta->id)->count());
        $this->assertSame(1, Postulacion::where('oferta_id', $otra->id)->count());
        $this->assertModelExists($otra);
    }

    public function test_elimina_una_oferta_sin_imagen(): void
    {
        $oferta = $this->crearOferta($this->empresa, ['imagen' => null]);

        $this->delete("/ofertas/{$oferta->id}")->assertRedirect(route('ofertas.index'));

        $this->assertModelMissing($oferta);
    }

    public function test_eliminar_un_id_inexistente_da_404(): void
    {
        $this->delete('/ofertas/9999')->assertNotFound();
        $this->delete('/ofertas/abc')->assertNotFound();
    }

    public function test_no_hay_get_destructivo(): void
    {
        $oferta = $this->ofertaConImagen();

        $this->get("/ofertas/{$oferta->id}/delete")->assertNotFound();
        $this->get("/ofertas/{$oferta->id}/destroy")->assertNotFound();
        $this->get("/ofertas/{$oferta->id}")->assertOk();
        $this->post("/ofertas/{$oferta->id}")->assertMethodNotAllowed();

        $this->assertModelExists($oferta);
        Storage::disk('public')->assertExists($oferta->imagen);
    }

    public function test_si_falla_el_borrado_conserva_registro_e_imagen(): void
    {
        $oferta = $this->ofertaConImagen();
        Oferta::deleting(fn () => throw new RuntimeException('Fallo simulado de la BD'));

        $this->delete("/ofertas/{$oferta->id}")
            ->assertRedirect(route('ofertas.show', $oferta))
            ->assertSessionHas('error');

        $this->assertModelExists($oferta);
        Storage::disk('public')->assertExists($oferta->imagen);
    }

    public function test_solo_borra_archivos_gestionados_por_la_app(): void
    {
        $disco = Storage::disk('public');
        $disco->put('otra-carpeta/ajeno.jpg', 'x');
        $disco->put('ofertas/../ajeno.jpg', 'x');

        $fuera = $this->crearOferta($this->empresa, ['imagen' => 'otra-carpeta/ajeno.jpg']);
        $traversal = $this->crearOferta($this->empresa, ['imagen' => 'ofertas/../ajeno.jpg']);

        $this->delete("/ofertas/{$fuera->id}")->assertRedirect(route('ofertas.index'));
        $this->delete("/ofertas/{$traversal->id}")->assertRedirect(route('ofertas.index'));

        $this->assertModelMissing($fuera);
        $this->assertModelMissing($traversal);
        $disco->assertExists('otra-carpeta/ajeno.jpg');
        $disco->assertExists('ajeno.jpg');
    }

    public function test_el_flujo_completo_no_deja_archivos_huerfanos(): void
    {
        $this->post('/ofertas', $this->datosFormulario($this->empresa, [
            'imagen' => UploadedFile::fake()->image('uno.jpg'),
        ]))->assertSessionHasNoErrors();
        $oferta = Oferta::sole();

        $this->put("/ofertas/{$oferta->id}", $this->datosFormulario($this->empresa, [
            'imagen' => UploadedFile::fake()->image('dos.png'),
        ]))->assertSessionHasNoErrors();
        $this->assertSame([$oferta->refresh()->imagen], Storage::disk('public')->allFiles());

        $this->delete("/ofertas/{$oferta->id}")->assertRedirect(route('ofertas.index'));

        $this->assertSame([], Storage::disk('public')->allFiles());
    }
}
