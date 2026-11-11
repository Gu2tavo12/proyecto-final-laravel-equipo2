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

    private function datosValidos(array $cambios = []): array
    {
        return array_merge([
            'nombre' => 'Nueva Empresa S.A.',
            'rubro' => 'Comercio',
            'correo_contacto' => 'rrhh@nueva.test',
            'telefono' => '2555-0101',
        ], $cambios);
    }

    public function test_formulario_de_registro_se_muestra_con_csrf(): void
    {
        $this->get(route('empresas.create'))
            ->assertOk()
            ->assertSee('name="_token"', false)
            ->assertSee(route('empresas.store'), false);
    }

    public function test_registra_empresa_con_datos_validos(): void
    {
        $respuesta = $this->post(route('empresas.store'), $this->datosValidos(['id' => 999]));

        $empresa = Empresa::firstWhere('correo_contacto', 'rrhh@nueva.test');
        $this->assertNotNull($empresa);
        $this->assertNotSame(999, $empresa->id);
        $respuesta->assertRedirect(route('empresas.show', $empresa))
            ->assertSessionHas('exito');
    }

    public function test_rechaza_campos_vacios_con_mensajes_en_espanol(): void
    {
        $this->from(route('empresas.create'))
            ->post(route('empresas.store'), [])
            ->assertRedirect(route('empresas.create'))
            ->assertSessionHasErrors([
                'nombre' => 'El nombre de la empresa es obligatorio.',
                'rubro' => 'El rubro es obligatorio.',
                'correo_contacto' => 'El correo de contacto es obligatorio.',
                'telefono' => 'El teléfono es obligatorio.',
            ]);

        $this->assertDatabaseCount('empresas', 0);
    }

    public function test_rechaza_nombre_y_correo_duplicados(): void
    {
        $this->crearEmpresa(['nombre' => 'Nueva Empresa S.A.', 'correo_contacto' => 'rrhh@nueva.test']);

        $this->post(route('empresas.store'), $this->datosValidos())
            ->assertSessionHasErrors(['nombre', 'correo_contacto']);

        $this->assertDatabaseCount('empresas', 1);
    }

    public function test_rechaza_telefono_con_formato_invalido(): void
    {
        foreach (['22223333', '2222-333', 'abcd-efgh', '2222-33334'] as $telefono) {
            $this->post(route('empresas.store'), $this->datosValidos(['telefono' => $telefono]))
                ->assertSessionHasErrors(['telefono' => 'El teléfono debe tener el formato 0000-0000.']);
        }

        $this->assertDatabaseCount('empresas', 0);
    }

    public function test_rechaza_textos_que_exceden_la_longitud(): void
    {
        $this->post(route('empresas.store'), $this->datosValidos([
            'nombre' => str_repeat('a', 151),
            'rubro' => str_repeat('b', 101),
            'correo_contacto' => str_repeat('c', 141).'@empresa.test',
        ]))->assertSessionHasErrors(['nombre', 'rubro', 'correo_contacto']);
    }
}
