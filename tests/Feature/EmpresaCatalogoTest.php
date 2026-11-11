<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Oferta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EmpresaCatalogoTest extends TestCase
{
    use RefreshDatabase;

    private function crearEmpresa(array $datos = []): Empresa
    {
        static $n = 0;
        $n++;

        return Empresa::create(array_merge([
            'nombre' => "Empresa Prueba {$n}",
            'rubro' => 'Tecnología',
            'correo_contacto' => "contacto{$n}@empresa.test",
            'telefono' => '2222-3333',
        ], $datos));
    }

    private function crearOferta(Empresa $empresa, array $datos = []): Oferta
    {
        return Oferta::factory()->create(array_merge(['empresa_id' => $empresa->id], $datos));
    }

    public function test_listado_muestra_empresas_con_conteo_de_ofertas(): void
    {
        $empresa = $this->crearEmpresa(['nombre' => 'Acme <b>Salvadoreña</b>']);
        $this->crearOferta($empresa);
        $this->crearOferta($empresa);

        $this->get(route('empresas.index'))
            ->assertOk()
            ->assertSee('Acme &lt;b&gt;Salvadoreña&lt;/b&gt;', false)
            ->assertViewHas('empresas', fn ($empresas) => $empresas->first()->ofertas_count === 2);
    }

    public function test_listado_vacio_muestra_mensaje(): void
    {
        $this->get(route('empresas.index'))
            ->assertOk()
            ->assertSee('No hay empresas registradas.');
    }

    public function test_listado_no_genera_consultas_n_mas_1(): void
    {
        foreach (range(1, 5) as $i) {
            $this->crearOferta($this->crearEmpresa());
        }

        DB::enableQueryLog();
        $this->get(route('empresas.index'))->assertOk();
        $consultas = collect(DB::getQueryLog())->filter(fn ($q) => str_contains($q['query'], 'ofertas'));

        $this->assertCount(1, $consultas);
    }

    public function test_ficha_muestra_datos_y_ofertas_con_enlace(): void
    {
        $empresa = $this->crearEmpresa();
        $oferta = $this->crearOferta($empresa, ['titulo' => 'Desarrollador PHP']);

        $this->get(route('empresas.show', $empresa))
            ->assertOk()
            ->assertSee($empresa->correo_contacto)
            ->assertSee('Desarrollador PHP')
            ->assertSee(route('ofertas.show', $oferta), false);
    }

    public function test_ficha_sin_ofertas_muestra_estado_vacio(): void
    {
        $empresa = $this->crearEmpresa();

        $this->get(route('empresas.show', $empresa))
            ->assertOk()
            ->assertSee('Esta empresa todavía no tiene ofertas registradas.');
    }

    public function test_ficha_inexistente_responde_404(): void
    {
        $this->get('/empresas/999')->assertNotFound();
    }
}
