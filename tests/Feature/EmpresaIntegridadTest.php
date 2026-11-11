<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Oferta;
use App\Models\Postulacion;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Integridad del catalogo de empresas y de sus ofertas.
 *
 * Se ejecuta con SQLite en memoria (phpunit.xml) y tambien puede correrse
 * contra una BD MySQL desechable sobreescribiendo DB_CONNECTION/DB_DATABASE.
 */
class EmpresaIntegridadTest extends TestCase
{
    use RefreshDatabase;

    private function empresa(string $sufijo = 'A'): Empresa
    {
        return Empresa::create([
            'nombre' => "Integridad {$sufijo}",
            'rubro' => 'Pruebas',
            'correo_contacto' => "integridad{$sufijo}@empresa.test",
            'telefono' => '2000-0000',
        ]);
    }

    public function test_delete_elimina_empresa_libre(): void
    {
        $empresa = $this->empresa();

        $this->delete(route('empresas.destroy', $empresa))->assertRedirect(route('empresas.index'));

        $this->assertDatabaseMissing('empresas', ['id' => $empresa->id]);
    }

    public function test_delete_rechaza_empresa_con_ofertas_y_conserva_relaciones(): void
    {
        $empresa = $this->empresa();
        $oferta = Oferta::factory()->create(['empresa_id' => $empresa->id]);
        $postulacion = Postulacion::factory()->create(['oferta_id' => $oferta->id]);

        $this->delete(route('empresas.destroy', $empresa))->assertSessionHas('error');

        $this->assertModelExists($empresa);
        $this->assertSame($empresa->id, $oferta->fresh()->empresa_id);
        $this->assertSame($oferta->id, $postulacion->fresh()->oferta_id);
    }

    public function test_get_no_elimina_empresas(): void
    {
        $empresa = $this->empresa();

        // Ninguna ruta GET borra: /empresas/{id} es la ficha y /destroy no existe
        $this->get(route('empresas.show', $empresa))->assertOk();
        $this->get("/empresas/{$empresa->id}/destroy")->assertNotFound();
        $this->get("/empresas/{$empresa->id}/delete")->assertNotFound();

        $this->assertModelExists($empresa);
    }

    public function test_fk_restrictiva_empresa_oferta_en_la_bd(): void
    {
        $empresa = $this->empresa();
        Oferta::factory()->create(['empresa_id' => $empresa->id]);

        $this->expectException(QueryException::class);

        // Borrado directo, sin pasar por el controlador
        Empresa::whereKey($empresa->id)->delete();
    }

    public function test_fk_en_cascada_oferta_postulaciones(): void
    {
        $empresa = $this->empresa();
        $oferta = Oferta::factory()->create(['empresa_id' => $empresa->id]);
        Postulacion::factory()->count(3)->create(['oferta_id' => $oferta->id]);

        $oferta->delete();

        $this->assertDatabaseCount('postulaciones', 0);
        $this->assertModelExists($empresa);
    }

    public function test_unicidad_compuesta_oferta_correo(): void
    {
        $empresa = $this->empresa();
        $oferta = Oferta::factory()->create(['empresa_id' => $empresa->id]);
        $otra = Oferta::factory()->create(['empresa_id' => $empresa->id]);
        Postulacion::factory()->create(['oferta_id' => $oferta->id, 'correo' => 'ana@correo.test']);

        // El mismo correo en otra oferta si se permite
        Postulacion::factory()->create(['oferta_id' => $otra->id, 'correo' => 'ana@correo.test']);

        $this->expectException(QueryException::class);
        Postulacion::factory()->create(['oferta_id' => $oferta->id, 'correo' => 'ana@correo.test']);
    }

    public function test_unicidad_de_nombre_y_correo_de_empresa_en_la_bd(): void
    {
        $this->empresa();

        $this->expectException(QueryException::class);
        $this->empresa(); // mismo nombre y correo
    }
}
