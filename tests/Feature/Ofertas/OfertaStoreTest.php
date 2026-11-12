<?php

namespace Tests\Feature\Ofertas;

use App\Models\Empresa;
use App\Models\Oferta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class OfertaStoreTest extends TestCase
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

    public function test_el_formulario_recibe_las_empresas_ordenadas_por_nombre(): void
    {
        $this->crearEmpresa('Zeta Consultores');
        $this->crearEmpresa('Beta Bancos');

        $respuesta = $this->get('/ofertas/create')->assertOk()->assertViewIs('ofertas.create');

        $this->assertSame(
            ['Acme Software', 'Beta Bancos', 'Zeta Consultores'],
            $respuesta->viewData('empresas')->pluck('nombre')->all(),
        );
        $respuesta->assertSee('name="_token"', false)->assertSee('enctype="multipart/form-data"', false);
    }

    public function test_sin_empresas_redirige_al_registro_de_empresas(): void
    {
        Empresa::query()->delete();

        $this->get('/ofertas/create')
            ->assertRedirect(route('empresas.create'))
            ->assertSessionHas('error');
    }

    public function test_registra_oferta_sin_imagen(): void
    {
        $respuesta = $this->post('/ofertas', $this->datosFormulario($this->empresa));

        $oferta = Oferta::sole();
        $respuesta->assertRedirect(route('ofertas.show', $oferta))
            ->assertSessionHas('exito', 'Oferta registrada correctamente.');
        $this->assertNull($oferta->imagen);
        $this->assertSame('1200.50', $oferta->salario_maximo);
        $this->assertStringEndsWith('img/oferta-default.svg', $oferta->imagen_url);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_registra_oferta_con_imagen_en_el_disco_public(): void
    {
        $this->post('/ofertas', $this->datosFormulario($this->empresa, [
            'imagen' => UploadedFile::fake()->image('logo.png', 400, 300),
        ]))->assertSessionHasNoErrors();

        $oferta = Oferta::sole();
        $this->assertStringStartsWith('ofertas/', $oferta->imagen);
        $this->assertStringEndsWith('.png', $oferta->imagen);
        Storage::disk('public')->assertExists($oferta->imagen);
        $this->assertStringEndsWith('storage/'.$oferta->imagen, $oferta->imagen_url);
    }

    public function test_pasantia_sin_salarios_se_registra_a_convenir(): void
    {
        $this->post('/ofertas', $this->datosFormulario($this->empresa, [
            'tipo' => 'pasantia',
            'salario_minimo' => '',
            'salario_maximo' => '',
        ]))->assertSessionHasNoErrors();

        $oferta = Oferta::sole();
        $this->assertNull($oferta->salario_minimo);
        $this->assertNull($oferta->salario_maximo);
        $this->assertSame('A convenir', $oferta->rango_salarial);
    }

    public function test_datos_vacios_regresan_al_formulario_con_errores_y_old(): void
    {
        $this->from('/ofertas/create')
            ->post('/ofertas', ['titulo' => 'Solo el título'])
            ->assertRedirect('/ofertas/create')
            ->assertSessionHasErrors(['empresa_id', 'descripcion', 'modalidad', 'tipo', 'fecha_publicacion', 'fecha_cierre', 'vacantes'])
            ->assertSessionHasInput('titulo', 'Solo el título');

        $this->assertSame(0, Oferta::count());
    }

    public function test_rechaza_rangos_y_fechas_invalidos(): void
    {
        $this->post('/ofertas', $this->datosFormulario($this->empresa, [
            'salario_minimo' => '1500',
            'salario_maximo' => '1000',
            'fecha_cierre' => today()->toDateString(),
        ]))->assertSessionHasErrors([
            'salario_maximo' => 'El salario máximo debe ser mayor o igual al salario mínimo.',
            'fecha_cierre' => 'La fecha de cierre debe ser posterior a la fecha de publicación.',
        ]);

        $this->assertSame(0, Oferta::count());
    }

    public function test_rechaza_vacantes_fuera_de_limite(): void
    {
        foreach (['0', '65536', '-3'] as $vacantes) {
            $this->post('/ofertas', $this->datosFormulario($this->empresa, ['vacantes' => $vacantes]))
                ->assertSessionHasErrors('vacantes');
        }

        $this->assertSame(0, Oferta::count());
    }

    public function test_rechaza_archivo_invalido_o_pesado_sin_guardar_nada(): void
    {
        $this->post('/ofertas', $this->datosFormulario($this->empresa, [
            'imagen' => UploadedFile::fake()->create('cv.pdf', 50, 'application/pdf'),
        ]))->assertSessionHasErrors(['imagen' => 'La imagen debe ser JPG, PNG o WebP.']);

        $this->post('/ofertas', $this->datosFormulario($this->empresa, [
            'imagen' => UploadedFile::fake()->image('enorme.jpg')->size(3000),
        ]))->assertSessionHasErrors(['imagen' => 'La imagen no puede pesar más de 2 MB.']);

        $this->assertSame(0, Oferta::count());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_si_falla_la_bd_se_elimina_la_imagen_nueva(): void
    {
        Oferta::creating(fn () => throw new RuntimeException('Fallo simulado de la BD'));

        $this->from('/ofertas/create')
            ->post('/ofertas', $this->datosFormulario($this->empresa, [
                'imagen' => UploadedFile::fake()->image('logo.jpg'),
            ]))
            ->assertRedirect('/ofertas/create')
            ->assertSessionHas('error');

        $this->assertSame(0, Oferta::count());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_si_no_se_puede_escribir_la_imagen_no_se_guarda_la_oferta(): void
    {
        $disco = Mockery::mock(Storage::disk('public'))->makePartial();
        $disco->shouldReceive('putFileAs')->andReturn(false);
        Storage::set('public', $disco);

        $this->from('/ofertas/create')
            ->post('/ofertas', $this->datosFormulario($this->empresa, [
                'imagen' => UploadedFile::fake()->image('logo.jpg'),
            ]))
            ->assertRedirect('/ofertas/create')
            ->assertSessionHasErrors(['imagen' => 'No se pudo guardar la imagen. Intenta de nuevo.']);

        $this->assertSame(0, Oferta::count());
    }
}
