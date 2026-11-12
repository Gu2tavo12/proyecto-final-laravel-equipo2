<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Oferta;
use App\Models\Postulacion;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

/**
 * Ofertas y postulaciones fijas para demostrar las reglas del caso.
 *
 * [SEEDER] Las fechas son relativas a hoy, asi cada migrate:fresh --seed deja
 * los mismos escenarios: una oferta activa por modalidad, una que cierra hoy,
 * dos cerradas, una pasantia sin salario y una oferta sin postulaciones.
 * Cada oferta recibe una cantidad distinta de postulaciones (7, 6, 5, 4, 3, 2,
 * 1 y 0) para que el ranking del reporte sea verificable sin empates.
 */
class DemostracionSeeder extends Seeder
{
    /**
     * Correo fijo para demostrar la regla "un correo por oferta": ya esta
     * postulado a "Desarrollador Laravel Senior", asi que repetirlo ahi
     * (aunque sea en mayusculas) debe fallar y en otra oferta debe pasar.
     */
    public const CORREO_DEMO = 'ana.demo@example.com';

    /**
     * Orden fijo de estados: con 28 postulaciones quedan 7 de cada estado.
     */
    private const ESTADOS = ['recibida', 'en_revision', 'aceptada', 'rechazada'];

    public function run(): void
    {
        $indiceEstado = 0;

        foreach ($this->ofertas() as $datos) {
            $postulaciones = $datos['postulaciones'];
            unset($datos['postulaciones']);

            $empresa = Empresa::where('nombre', $datos['empresa'])->firstOrFail();
            unset($datos['empresa']);

            $oferta = Oferta::create([
                ...$datos,
                'empresa_id' => $empresa->id,
                'fecha_publicacion' => today()->addDays($datos['fecha_publicacion']),
                'fecha_cierre' => today()->addDays($datos['fecha_cierre']),
                'imagen' => null,
            ]);

            if ($postulaciones === 0) {
                continue;
            }

            // Estados rotativos: el conteo por estado tambien es verificable.
            $estados = [];
            for ($i = 0; $i < $postulaciones; $i++) {
                $estados[] = ['estado' => self::ESTADOS[$indiceEstado++ % count(self::ESTADOS)]];
            }

            Postulacion::factory()
                ->count($postulaciones)
                ->paraOferta($oferta)
                ->state(new Sequence(...$estados))
                ->create();
        }

        // Candidata conocida en la oferta con mas postulaciones (reemplaza a la primera).
        Oferta::where('titulo', 'Desarrollador Laravel Senior')->sole()
            ->postulaciones()->orderBy('id')->first()
            ->update([
                'nombre_candidato' => 'Ana Demo',
                'correo' => self::CORREO_DEMO,
            ]);
    }

    /**
     * Escenarios de demostracion. Las fechas son dias relativos a hoy.
     *
     * @return array<int, array<string, mixed>>
     */
    private function ofertas(): array
    {
        return [
            [
                'titulo' => 'Desarrollador Laravel Senior',
                'empresa' => 'TecnoSoluciones Cuscatlán S.A. de C.V.',
                'descripcion' => 'Desarrollo y mantenimiento de aplicaciones Laravel con MySQL. Se requieren 3 años de experiencia.',
                'modalidad' => 'presencial',
                'tipo' => 'empleo',
                'salario_minimo' => 1200,
                'salario_maximo' => 1800,
                'fecha_publicacion' => -20,
                'fecha_cierre' => 25,
                'vacantes' => 2,
                'postulaciones' => 7,
            ],
            [
                'titulo' => 'Científico de Datos Junior',
                'empresa' => 'Financiera Volcán Azul S.A.',
                'descripcion' => 'Análisis de cartera y modelos predictivos en Python. Trabajo 100 % remoto.',
                'modalidad' => 'remota',
                'tipo' => 'empleo',
                'salario_minimo' => 900,
                'salario_maximo' => 1300,
                'fecha_publicacion' => -15,
                'fecha_cierre' => 30,
                'vacantes' => 1,
                'postulaciones' => 6,
            ],
            [
                'titulo' => 'Ingeniero de Infraestructura Cloud',
                'empresa' => 'RedNova Telecomunicaciones S.A. de C.V.',
                'descripcion' => 'Administración de servidores en la nube y automatización de despliegues. Modalidad híbrida.',
                'modalidad' => 'hibrida',
                'tipo' => 'empleo',
                'salario_minimo' => 1100,
                'salario_maximo' => 1600,
                'fecha_publicacion' => -10,
                'fecha_cierre' => 20,
                'vacantes' => 1,
                'postulaciones' => 5,
            ],
            [
                // Pasantia sin salario: el modelo la muestra como "A convenir".
                'titulo' => 'Pasantía en Desarrollo Web',
                'empresa' => 'Lempa Consultores Asociados',
                'descripcion' => 'Pasantía de seis meses para estudiantes de último año. Apoyo en sitios web con Laravel.',
                'modalidad' => 'remota',
                'tipo' => 'pasantia',
                'salario_minimo' => null,
                'salario_maximo' => null,
                'fecha_publicacion' => -12,
                'fecha_cierre' => 15,
                'vacantes' => 3,
                'postulaciones' => 4,
            ],
            [
                // Cierra hoy: sigue activa y todavia acepta postulaciones.
                'titulo' => 'Técnico de Soporte en Sitio',
                'empresa' => 'Manufacturas Izalco S.A. de C.V.',
                'descripcion' => 'Soporte a usuarios, mantenimiento de equipos e inventario de hardware en planta.',
                'modalidad' => 'presencial',
                'tipo' => 'empleo',
                'salario_minimo' => 600,
                'salario_maximo' => 750,
                'fecha_publicacion' => -30,
                'fecha_cierre' => 0,
                'vacantes' => 2,
                'postulaciones' => 3,
            ],
            [
                // Cerro ayer: ya no acepta postulaciones nuevas.
                'titulo' => 'Auxiliar Administrativo',
                'empresa' => 'Centro Médico Brisas del Lago',
                'descripcion' => 'Gestión de agenda, archivo y atención a pacientes en recepción.',
                'modalidad' => 'presencial',
                'tipo' => 'empleo',
                'salario_minimo' => 450,
                'salario_maximo' => 550,
                'fecha_publicacion' => -40,
                'fecha_cierre' => -1,
                'vacantes' => 1,
                'postulaciones' => 2,
            ],
            [
                'titulo' => 'Contador General',
                'empresa' => 'Financiera Volcán Azul S.A.',
                'descripcion' => 'Elaboración de estados financieros y declaraciones de impuestos.',
                'modalidad' => 'hibrida',
                'tipo' => 'empleo',
                'salario_minimo' => 1000,
                'salario_maximo' => 1400,
                'fecha_publicacion' => -60,
                'fecha_cierre' => -20,
                'vacantes' => 1,
                'postulaciones' => 1,
            ],
            [
                // Oferta activa sin postulaciones (estado vacio del filtro).
                'titulo' => 'Pasantía en Diseño UX/UI',
                'empresa' => 'TecnoSoluciones Cuscatlán S.A. de C.V.',
                'descripcion' => 'Prototipado de interfaces y pruebas de usabilidad con usuarios reales.',
                'modalidad' => 'hibrida',
                'tipo' => 'pasantia',
                'salario_minimo' => 200,
                'salario_maximo' => 300,
                'fecha_publicacion' => -2,
                'fecha_cierre' => 40,
                'vacantes' => 2,
                'postulaciones' => 0,
            ],
        ];
    }
}
