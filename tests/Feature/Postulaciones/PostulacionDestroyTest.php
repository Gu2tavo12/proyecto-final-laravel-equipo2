<?php

namespace Tests\Feature\Postulaciones;

use App\Models\Oferta;
use App\Models\Postulacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PostulacionDestroyTest extends TestCase
{
    use CreaDatosDePostulaciones;
    use RefreshDatabase;

    private Oferta $oferta;

    private Postulacion $postulacion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->oferta = $this->crearOferta(['titulo' => 'Desarrollador Laravel']);
        $this->postulacion = $this->crearPostulacion($this->oferta, ['nombre_candidato' => 'Ana Martínez']);
    }

    public function test_la_ficha_presenta_confirmacion_con_csrf_y_delete(): void
    {
        $this->get(route('postulaciones.show', $this->postulacion))
            ->assertOk()
            ->assertSee('action="'.route('postulaciones.destroy', $this->postulacion).'"', false)
            ->assertSee('data-confirmar-eliminar=', false)
            ->assertSee('name="_token"', false)
            ->assertSee('name="_method" value="DELETE"', false);
    }

    public function test_elimina_solo_la_postulacion_indicada_y_conserva_la_oferta(): void
    {
        $otra = $this->crearPostulacion($this->oferta);

        $this->delete(route('postulaciones.destroy', $this->postulacion))
            ->assertRedirect(route('postulaciones.index', ['oferta_id' => $this->oferta->id]))
            ->assertSessionHas('exito', 'Postulación de Ana Martínez eliminada correctamente.');

        $this->assertModelMissing($this->postulacion);
        $this->assertModelExists($otra);
        $this->assertModelExists($this->oferta);
        $this->assertSame(1, $this->oferta->postulaciones()->count());
    }

    public function test_get_no_tiene_efecto_destructivo(): void
    {
        // GET a la URL del recurso muestra la ficha; GET a .../destroy no existe.
        $this->get('/postulaciones/'.$this->postulacion->id)->assertOk();
        $this->get('/postulaciones/'.$this->postulacion->id.'/destroy')->assertNotFound();
        $this->post('/postulaciones/'.$this->postulacion->id)->assertMethodNotAllowed();

        $this->assertModelExists($this->postulacion);
    }

    public function test_eliminar_postulacion_inexistente_responde_404(): void
    {
        $this->delete('/postulaciones/999')->assertNotFound();

        $this->assertSame(1, Postulacion::count());
    }

    public function test_rutas_del_recurso_para_la_integracion(): void
    {
        $esperadas = [
            'postulaciones.index' => ['GET', 'postulaciones'],
            'postulaciones.create' => ['GET', 'postulaciones/create'],
            'postulaciones.store' => ['POST', 'postulaciones'],
            'postulaciones.show' => ['GET', 'postulaciones/{postulacion}'],
            'postulaciones.edit' => ['GET', 'postulaciones/{postulacion}/edit'],
            'postulaciones.update' => ['PUT', 'postulaciones/{postulacion}'],
            'postulaciones.destroy' => ['DELETE', 'postulaciones/{postulacion}'],
        ];

        foreach ($esperadas as $nombre => [$metodo, $uri]) {
            $ruta = Route::getRoutes()->getByName($nombre);
            $this->assertNotNull($ruta, $nombre);
            $this->assertContains($metodo, $ruta->methods(), $nombre);
            $this->assertSame($uri, $ruta->uri(), $nombre);
        }

        // Enlaces que usara la ficha de oferta: registrar con preseleccion y listado filtrado
        $this->assertStringEndsWith('/postulaciones/create?oferta_id='.$this->oferta->id, route('postulaciones.create', ['oferta_id' => $this->oferta->id]));
        $this->get(route('postulaciones.create', ['oferta_id' => $this->oferta->id]))
            ->assertSee('<option value="'.$this->oferta->id.'" selected', false);
        $this->get(route('postulaciones.index', ['oferta_id' => $this->oferta->id]))
            ->assertSee('href="'.route('postulaciones.create', ['oferta_id' => $this->oferta->id]).'"', false)
            ->assertSee('href="'.route('postulaciones.show', $this->postulacion).'"', false)
            ->assertSee('href="'.route('ofertas.show', $this->oferta).'"', false);
    }
}
