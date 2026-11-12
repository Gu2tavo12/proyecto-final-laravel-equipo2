<?php

namespace Tests\Feature\Ofertas;

use App\Models\Empresa;
use App\Models\Oferta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class OfertaIndexTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $acme;

    private Empresa $globex;

    protected function setUp(): void
    {
        parent::setUp();

        $this->acme = $this->crearEmpresa('Acme Software', 'rrhh@acme.com');
        $this->globex = $this->crearEmpresa('Globex Finanzas', 'rrhh@globex.com');
    }

    private function crearEmpresa(string $nombre, string $correo): Empresa
    {
        return Empresa::create([
            'nombre' => $nombre,
            'rubro' => 'Tecnología',
            'correo_contacto' => $correo,
            'telefono' => '2222-3333',
        ]);
    }

    private function crearOferta(Empresa $empresa, array $atributos = []): Oferta
    {
        return Oferta::factory()->create(['empresa_id' => $empresa->id, ...$atributos]);
    }

    /**
     * IDs de las ofertas que recibio la vista, en el orden mostrado.
     *
     * @return array<int, int>
     */
    private function idsListados(string $url): array
    {
        $respuesta = $this->get($url)->assertOk()->assertViewIs('ofertas.index');

        return $respuesta->viewData('ofertas')->pluck('id')->all();
    }

    public function test_sin_filtros_lista_todas_paginadas_de_diez_mas_recientes_primero(): void
    {
        foreach (range(1, 12) as $dia) {
            $this->crearOferta($this->acme, ['fecha_publicacion' => today()->subDays($dia)]);
        }

        $respuesta = $this->get('/ofertas')->assertOk();
        $ofertas = $respuesta->viewData('ofertas');

        $this->assertInstanceOf(LengthAwarePaginator::class, $ofertas);
        $this->assertSame(12, $ofertas->total());
        $this->assertCount(10, $ofertas->items());
        $fechas = $ofertas->getCollection()->map(fn (Oferta $o) => $o->fecha_publicacion->toDateString())->all();
        $ordenadas = $fechas;
        rsort($ordenadas);
        $this->assertSame($ordenadas, $fechas);
        $this->assertTrue($ofertas->first()->relationLoaded('empresa'));
    }

    public function test_busca_por_titulo(): void
    {
        $laravel = $this->crearOferta($this->acme, ['titulo' => 'Desarrollador Laravel']);
        $this->crearOferta($this->acme, ['titulo' => 'Analista Contable']);

        $this->assertSame([$laravel->id], $this->idsListados('/ofertas?q=laravel'));
    }

    public function test_busca_por_nombre_de_empresa(): void
    {
        $deGlobex = $this->crearOferta($this->globex, ['titulo' => 'Analista Contable']);
        $this->crearOferta($this->acme, ['titulo' => 'Soporte Técnico']);

        $this->assertSame([$deGlobex->id], $this->idsListados('/ofertas?q=globex'));
    }

    public function test_filtra_por_modalidad_y_por_tipo(): void
    {
        $remota = $this->crearOferta($this->acme, ['modalidad' => 'remota', 'tipo' => 'empleo']);
        $pasantia = $this->crearOferta($this->acme, ['modalidad' => 'presencial', 'tipo' => 'pasantia']);

        $this->assertSame([$remota->id], $this->idsListados('/ofertas?modalidad=remota'));
        $this->assertSame([$pasantia->id], $this->idsListados('/ofertas?tipo=pasantia'));
    }

    public function test_combina_busqueda_y_filtros_respetando_el_grupo_or(): void
    {
        // Coincide por titulo y por filtros: debe aparecer.
        $esperada = $this->crearOferta($this->acme, ['titulo' => 'Dev Acme', 'modalidad' => 'remota', 'tipo' => 'empleo', 'fecha_publicacion' => today()]);
        // Coincide por empresa (OR) pero no por modalidad: no debe aparecer.
        $this->crearOferta($this->acme, ['titulo' => 'Contador', 'modalidad' => 'presencial', 'tipo' => 'empleo']);
        // Coincide por empresa y modalidad pero no por tipo: no debe aparecer.
        $this->crearOferta($this->acme, ['titulo' => 'Pasante', 'modalidad' => 'remota', 'tipo' => 'pasantia']);
        // Coincide por filtros pero no por texto: no debe aparecer.
        $this->crearOferta($this->globex, ['titulo' => 'Cajero', 'modalidad' => 'remota', 'tipo' => 'empleo']);

        $this->assertSame([$esperada->id], $this->idsListados('/ofertas?q=acme&modalidad=remota&tipo=empleo'));
    }

    public function test_ignora_valores_de_filtro_no_permitidos(): void
    {
        $this->crearOferta($this->acme);
        $this->crearOferta($this->globex);

        $respuesta = $this->get('/ofertas?modalidad=teletrabajo&tipo[]=empleo')->assertOk();

        $this->assertSame(2, $respuesta->viewData('ofertas')->total());
        $this->assertSame(['q' => '', 'modalidad' => '', 'tipo' => ''], $respuesta->viewData('filtros'));
    }

    public function test_recorta_la_busqueda_a_100_caracteres(): void
    {
        $respuesta = $this->get('/ofertas?q='.str_repeat('a', 300))->assertOk();

        $this->assertSame(100, mb_strlen($respuesta->viewData('filtros')['q']));
    }

    public function test_los_comodines_y_comillas_no_rompen_la_consulta(): void
    {
        $this->crearOferta($this->acme, ['titulo' => "Desarrollador O'Brien"]);

        $this->assertCount(1, $this->idsListados('/ofertas?q='.urlencode("O'Brien")));
        $this->assertCount(0, $this->idsListados('/ofertas?q='.urlencode("' OR 1=1 --")));
    }

    public function test_la_paginacion_conserva_los_filtros(): void
    {
        foreach (range(1, 13) as $dia) {
            $this->crearOferta($this->acme, ['modalidad' => 'remota', 'fecha_publicacion' => today()->subDays($dia)]);
        }
        $this->crearOferta($this->acme, ['modalidad' => 'presencial']);

        $pagina1 = $this->get('/ofertas?q=acme&modalidad=remota')->assertOk();
        $ofertas = $pagina1->viewData('ofertas');

        $this->assertSame(13, $ofertas->total());
        $this->assertStringContainsString('modalidad=remota', $ofertas->nextPageUrl());
        $this->assertStringContainsString('q=acme', $ofertas->nextPageUrl());
        $pagina1->assertSee('modalidad=remota', false);

        $pagina2 = $this->get('/ofertas?q=acme&modalidad=remota&page=2')->assertOk()->viewData('ofertas');
        $this->assertCount(3, $pagina2->items());
        $this->assertTrue($pagina2->getCollection()->every(fn (Oferta $o) => $o->modalidad === 'remota'));
    }
}
