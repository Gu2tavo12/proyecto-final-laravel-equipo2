<?php

namespace Tests\Feature\Postulaciones;

use App\Models\Empresa;
use App\Models\Oferta;
use App\Models\Postulacion;

/**
 * Ayudantes compartidos por los tests de postulaciones.
 */
trait CreaDatosDePostulaciones
{
    protected function crearOferta(array $atributos = [], string $empresa = 'Acme Software'): Oferta
    {
        $empresa = Empresa::firstOrCreate(['nombre' => $empresa], [
            'rubro' => 'Tecnología',
            'correo_contacto' => 'rrhh@'.str()->slug($empresa).'.com',
            'telefono' => '2222-3333',
        ]);

        return Oferta::factory()->create(['empresa_id' => $empresa->id, ...$atributos]);
    }

    protected function crearPostulacion(Oferta $oferta, array $atributos = []): Postulacion
    {
        return Postulacion::factory()->paraOferta($oferta)->create($atributos);
    }
}
