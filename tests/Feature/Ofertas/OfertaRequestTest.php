<?php

namespace Tests\Feature\Ofertas;

use App\Http\Requests\StoreOfertaRequest;
use App\Http\Requests\UpdateOfertaRequest;
use App\Models\Empresa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OfertaRequestTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empresa = Empresa::create([
            'nombre' => 'Empresa de Prueba',
            'rubro' => 'Tecnología',
            'correo_contacto' => 'rrhh@prueba.com',
            'telefono' => '2222-3333',
        ]);
    }

    private function datosValidos(array $cambios = []): array
    {
        return array_merge([
            'empresa_id' => $this->empresa->id,
            'titulo' => 'Desarrollador Laravel',
            'descripcion' => 'Buscamos desarrollador con experiencia.',
            'modalidad' => 'remota',
            'tipo' => 'empleo',
            'salario_minimo' => '800',
            'salario_maximo' => '1200.50',
            'fecha_publicacion' => '2026-11-11',
            'fecha_cierre' => '2026-12-11',
            'vacantes' => '2',
        ], $cambios);
    }

    private function validar(array $datos, string $request = StoreOfertaRequest::class): \Illuminate\Validation\Validator
    {
        $formRequest = new $request;

        return Validator::make($datos, $formRequest->rules(), $formRequest->messages(), $formRequest->attributes());
    }

    public function test_acepta_datos_validos_al_crear_y_al_editar(): void
    {
        $this->assertTrue($this->validar($this->datosValidos())->passes());
        $this->assertTrue($this->validar($this->datosValidos(), UpdateOfertaRequest::class)->passes());
    }

    public function test_pasantia_permite_omitir_salarios(): void
    {
        $datos = $this->datosValidos(['tipo' => 'pasantia', 'salario_minimo' => null, 'salario_maximo' => null]);

        $this->assertTrue($this->validar($datos)->passes());
    }

    public function test_pasantia_permite_un_solo_salario(): void
    {
        $datos = $this->datosValidos(['tipo' => 'pasantia', 'salario_minimo' => null, 'salario_maximo' => '300']);

        $this->assertTrue($this->validar($datos)->passes());
    }

    public function test_empleo_exige_ambos_salarios(): void
    {
        $validador = $this->validar($this->datosValidos(['salario_minimo' => '', 'salario_maximo' => null]));

        $this->assertTrue($validador->fails());
        $this->assertSame(
            'El campo salario mínimo es obligatorio cuando la oferta es de empleo.',
            $validador->errors()->first('salario_minimo'),
        );
        $this->assertTrue($validador->errors()->has('salario_maximo'));
    }

    public function test_datos_vacios_muestran_mensajes_en_espanol(): void
    {
        $validador = $this->validar([]);

        $this->assertTrue($validador->fails());
        $this->assertSame('El campo título es obligatorio.', $validador->errors()->first('titulo'));
        $this->assertSame('El campo empresa es obligatorio.', $validador->errors()->first('empresa_id'));
        $this->assertEqualsCanonicalizing(
            ['empresa_id', 'titulo', 'descripcion', 'modalidad', 'tipo', 'fecha_publicacion', 'fecha_cierre', 'vacantes'],
            $validador->errors()->keys(),
        );
    }

    /**
     * @return array<string, array{0: array<string, mixed>, 1: string}>
     */
    public static function datosInvalidos(): array
    {
        return [
            'empresa inexistente' => [['empresa_id' => 9999], 'empresa_id'],
            'titulo de 151 caracteres' => [['titulo' => str_repeat('a', 151)], 'titulo'],
            'descripcion demasiado larga' => [['descripcion' => str_repeat('a', 16001)], 'descripcion'],
            'modalidad fuera del enum' => [['modalidad' => 'teletrabajo'], 'modalidad'],
            'tipo fuera del enum' => [['tipo' => 'voluntariado'], 'tipo'],
            'fecha de publicacion invalida' => [['fecha_publicacion' => '2026-02-30'], 'fecha_publicacion'],
            'cierre igual a publicacion' => [['fecha_cierre' => '2026-11-11'], 'fecha_cierre'],
            'cierre anterior a publicacion' => [['fecha_cierre' => '2026-11-01'], 'fecha_cierre'],
            'cero vacantes' => [['vacantes' => '0'], 'vacantes'],
            'vacantes sobre smallint' => [['vacantes' => '65536'], 'vacantes'],
            'vacantes decimales' => [['vacantes' => '1.5'], 'vacantes'],
            'salario negativo' => [['salario_minimo' => '-1'], 'salario_minimo'],
            'salario con 3 decimales' => [['salario_minimo' => '800.123'], 'salario_minimo'],
            'salario sobre decimal(10,2)' => [['salario_maximo' => '100000000'], 'salario_maximo'],
            'salario no numerico' => [['salario_minimo' => 'mil'], 'salario_minimo'],
            'minimo mayor que maximo' => [['salario_minimo' => '1500', 'salario_maximo' => '1200'], 'salario_maximo'],
        ];
    }

    #[DataProvider('datosInvalidos')]
    public function test_rechaza_datos_invalidos(array $cambios, string $campo): void
    {
        $validador = $this->validar($this->datosValidos($cambios));

        $this->assertTrue($validador->fails());
        $this->assertTrue($validador->errors()->has($campo), "Se esperaba error en {$campo}");
    }

    public function test_acepta_vacantes_en_el_limite(): void
    {
        $this->assertTrue($this->validar($this->datosValidos(['vacantes' => '1']))->passes());
        $this->assertTrue($this->validar($this->datosValidos(['vacantes' => '65535']))->passes());
    }

    public function test_acepta_imagenes_jpg_png_y_webp(): void
    {
        foreach (['foto.jpg', 'foto.png', 'foto.webp'] as $nombre) {
            $datos = $this->datosValidos(['imagen' => UploadedFile::fake()->image($nombre)]);

            $this->assertTrue($this->validar($datos)->passes(), "Debe aceptar {$nombre}");
        }
    }

    public function test_rechaza_imagen_de_mas_de_2_mb(): void
    {
        $datos = $this->datosValidos(['imagen' => UploadedFile::fake()->image('foto.jpg')->size(2049)]);
        $validador = $this->validar($datos);

        $this->assertTrue($validador->fails());
        $this->assertSame('La imagen no puede pesar más de 2 MB.', $validador->errors()->first('imagen'));
    }

    public function test_rechaza_archivo_que_no_es_imagen_aunque_tenga_extension_jpg(): void
    {
        // Archivo real de texto plano renombrado a .jpg: fileinfo detecta text/plain.
        $ruta = tempnam(sys_get_temp_dir(), 'oferta');
        file_put_contents($ruta, 'esto no es una imagen');
        $falso = new UploadedFile($ruta, 'foto.jpg', 'image/jpeg', null, true);

        $this->assertTrue($this->validar($this->datosValidos(['imagen' => $falso]))->fails());
    }

    public function test_rechaza_formatos_no_permitidos(): void
    {
        $gif = UploadedFile::fake()->image('animacion.gif');
        $pdf = UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf');

        $this->assertTrue($this->validar($this->datosValidos(['imagen' => $gif]))->fails());
        $this->assertTrue($this->validar($this->datosValidos(['imagen' => $pdf]))->fails());
    }
}
