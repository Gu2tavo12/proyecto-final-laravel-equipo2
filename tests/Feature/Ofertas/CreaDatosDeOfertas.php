<?php

namespace Tests\Feature\Ofertas;

use App\Models\Empresa;
use App\Models\Oferta;

/**
 * Ayudantes compartidos por los tests de escritura de ofertas.
 */
trait CreaDatosDeOfertas
{
    protected function crearEmpresa(string $nombre = 'Acme Software', ?string $correo = null): Empresa
    {
        return Empresa::create([
            'nombre' => $nombre,
            'rubro' => 'Tecnología',
            'correo_contacto' => $correo ?? 'rrhh@'.str()->slug($nombre).'.com',
            'telefono' => '2222-3333',
        ]);
    }

    protected function crearOferta(Empresa $empresa, array $atributos = []): Oferta
    {
        return Oferta::factory()->create(['empresa_id' => $empresa->id, ...$atributos]);
    }

    /**
     * Datos validos del formulario (como los envia el navegador).
     *
     * @return array<string, mixed>
     */
    protected function datosFormulario(Empresa $empresa, array $cambios = []): array
    {
        return array_merge([
            'empresa_id' => (string) $empresa->id,
            'titulo' => 'Desarrollador Laravel',
            'descripcion' => 'Buscamos desarrollador con experiencia en Laravel.',
            'modalidad' => 'remota',
            'tipo' => 'empleo',
            'salario_minimo' => '800',
            'salario_maximo' => '1200.50',
            'fecha_publicacion' => today()->toDateString(),
            'fecha_cierre' => today()->addMonth()->toDateString(),
            'vacantes' => '2',
        ], $cambios);
    }
}
