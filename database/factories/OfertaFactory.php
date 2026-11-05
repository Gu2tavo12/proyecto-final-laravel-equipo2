<?php

namespace Database\Factories;

use App\Models\Empresa;
use App\Models\Oferta;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Oferta>
 */
class OfertaFactory extends Factory
{
    /**
     * Puestos de TI y administracion.
     */
    private const TITULOS = [
        'Desarrollador Backend PHP',
        'Desarrollador Frontend JavaScript',
        'Desarrollador Full Stack Laravel',
        'Analista de Datos',
        'Soporte Técnico',
        'Pasante de Desarrollo Web',
        'Diseñador UX/UI',
        'Administrador de Redes',
        'QA Tester',
        'Administrador de Base de Datos',
        'Ingeniero DevOps',
        'Analista de Sistemas',
        'Especialista en Ciberseguridad',
        'Asistente Administrativo',
        'Analista Contable',
        'Coordinador de Proyectos TI',
        'Ejecutivo de Atención al Cliente',
    ];

    /**
     * Define the model's default state.
     *
     * Los atributos que dependen de otros son closures: Laravel los evalua
     * al final, con los valores ya combinados con los estados.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // No hay EmpresaFactory: las empresas son el catalogo fijo de EmpresaSeeder,
            // que siempre se ejecuta antes.
            'empresa_id' => fn () => Empresa::inRandomOrder()->value('id'),
            'titulo' => fake()->randomElement(self::TITULOS),
            'descripcion' => fn (array $attributes) => $this->descripcion(
                $attributes['titulo'],
                $attributes['modalidad'],
            ),
            'modalidad' => fake()->randomElement(['presencial', 'remota', 'hibrida']),
            'tipo' => fake()->randomElement(['empleo', 'pasantia']),
            'salario_minimo' => fake()->numberBetween(450, 1200),
            'salario_maximo' => fn (array $attributes) => $attributes['salario_minimo'] === null
                ? null
                : $attributes['salario_minimo'] + fake()->numberBetween(100, 1000),
            'fecha_publicacion' => today()->subDays(fake()->numberBetween(0, 60)),
            // Siempre entre +5 y +60 dias despues de la publicacion. El minimo se
            // sube hasta hoy si hace falta, para que la oferta normal quede activa.
            'fecha_cierre' => function (array $attributes) {
                $publicacion = Carbon::parse($attributes['fecha_publicacion']);
                $diasHastaHoy = (int) $publicacion->diffInDays(today());

                return $publicacion->copy()->addDays(
                    fake()->numberBetween(max(5, $diasHastaHoy), 60)
                );
            },
            'vacantes' => fake()->numberBetween(1, 10),
            'imagen' => null, // Se mostrara la imagen por defecto
        ];
    }

    /**
     * Estado: pasantia sin salario o con un estipendio bajo (150-400).
     */
    public function pasantia(): static
    {
        return $this->state(function (array $attributes) {
            if (fake()->boolean()) {
                return [
                    'tipo' => 'pasantia',
                    'salario_minimo' => null,
                    'salario_maximo' => null,
                ];
            }

            $minimo = fake()->numberBetween(150, 400);

            return [
                'tipo' => 'pasantia',
                'salario_minimo' => $minimo,
                'salario_maximo' => fake()->numberBetween($minimo, 400),
            ];
        });
    }

    /**
     * Estado: oferta cerrada (fecha de cierre en el pasado,
     * pero posterior a la fecha de publicacion).
     */
    public function cerrada(): static
    {
        return $this->state(function (array $attributes) {
            $publicacion = today()->subDays(fake()->numberBetween(20, 60));
            $diasHastaHoy = (int) $publicacion->diffInDays(today());

            return [
                'fecha_publicacion' => $publicacion,
                // Entre +5 dias despues de publicar y ayer
                'fecha_cierre' => $publicacion->copy()->addDays(
                    fake()->numberBetween(5, $diasHastaHoy - 1)
                ),
            ];
        });
    }

    /**
     * Arma una descripcion de 2 o 3 frases en espanol (plantillas + faker).
     */
    private function descripcion(string $titulo, string $modalidad): string
    {
        $modalidades = [
            'presencial' => 'presencial',
            'remota' => 'remota',
            'hibrida' => 'híbrida',
        ];

        $frases = [
            "Buscamos {$titulo} para unirse a nuestro equipo en modalidad {$modalidades[$modalidad]}.",
        ];

        $complementos = [
            'Se requiere experiencia mínima de '.fake()->numberBetween(2, 4).' años en puestos similares.',
            'Valoramos la capacidad de trabajo en equipo y la buena comunicación.',
            'Ofrecemos un ambiente de trabajo colaborativo y oportunidades de crecimiento.',
            'Es indispensable la disponibilidad para incorporarse de inmediato.',
            'Se brindará capacitación inicial sobre nuestros procesos internos.',
            'Deseable conocimiento de inglés técnico a nivel intermedio.',
        ];

        foreach (fake()->randomElements($complementos, fake()->numberBetween(1, 2)) as $frase) {
            $frases[] = $frase;
        }

        return implode(' ', $frases);
    }
}
