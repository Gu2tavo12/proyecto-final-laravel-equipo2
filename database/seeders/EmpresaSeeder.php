<?php

namespace Database\Seeders;

use App\Models\Empresa;
use Illuminate\Database\Seeder;

class EmpresaSeeder extends Seeder
{
    /**
     * Catalogo fijo de empresas ficticias salvadorenas.
     *
     * [SEEDER] Las empresas son un catalogo: datos fijos y coherentes,
     * insertados con Eloquent (Empresa::create), no con SQL directo.
     */
    public function run(): void
    {
        $empresas = [
            [
                'nombre' => 'TecnoSoluciones Cuscatlán S.A. de C.V.',
                'rubro' => 'Tecnología',
                'correo_contacto' => 'rrhh@tecnosoluciones-cuscatlan.com.sv',
                'telefono' => '2250-1234',
            ],
            [
                'nombre' => 'Financiera Volcán Azul S.A.',
                'rubro' => 'Banca',
                'correo_contacto' => 'rrhh@volcanazul-financiera.com.sv',
                'telefono' => '2245-6789',
            ],
            [
                'nombre' => 'RedNova Telecomunicaciones S.A. de C.V.',
                'rubro' => 'Telecomunicaciones',
                'correo_contacto' => 'rrhh@rednova-telecom.com.sv',
                'telefono' => '2208-4455',
            ],
            [
                'nombre' => 'Lempa Consultores Asociados',
                'rubro' => 'Consultoría',
                'correo_contacto' => 'rrhh@lempaconsultores.com.sv',
                'telefono' => '2263-7810',
            ],
            [
                'nombre' => 'Manufacturas Izalco S.A. de C.V.',
                'rubro' => 'Manufactura',
                'correo_contacto' => 'rrhh@manufacturasizalco.com.sv',
                'telefono' => '2451-3307',
            ],
            [
                'nombre' => 'Centro Médico Brisas del Lago',
                'rubro' => 'Salud',
                'correo_contacto' => 'rrhh@brisasdellago-medico.com.sv',
                'telefono' => '2296-1450',
            ],
        ];

        foreach ($empresas as $empresa) {
            Empresa::create($empresa);
        }
    }
}
