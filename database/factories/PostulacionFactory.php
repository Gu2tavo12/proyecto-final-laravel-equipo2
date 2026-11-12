<?php

namespace Database\Factories;

use App\Models\Oferta;
use App\Models\Postulacion;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Postulacion>
 */
class PostulacionFactory extends Factory
{
    /**
     * Carreras de los candidatos.
     */
    private const CARRERAS = [
        'Ingeniería en Sistemas',
        'Ingeniería Informática',
        'Técnico en Desarrollo de Software',
        'Licenciatura en Diseño Gráfico',
        'Administración de Empresas',
        'Mercadeo',
        'Licenciatura en Contaduría Pública',
        'Técnico en Redes Informáticas',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // El DatabaseSeeder siempre indica la oferta; esto es solo el valor por defecto
            'oferta_id' => fn () => Oferta::inRandomOrder()->value('id'),
            'nombre_candidato' => fake()->name(),
            // unique(): no repite correos, asi se respeta el unique(oferta_id, correo)
            'correo' => fake()->unique()->safeEmail(),
            'carrera' => fake()->randomElement(self::CARRERAS),
            'enlace_cv' => fake()->boolean()
                ? 'https://drive.google.com/file/d/'.Str::random(28).'/view'
                : 'https://github.com/'.Str::slug(fake()->userName()),
            'fecha_postulacion' => today()->subDays(fake()->numberBetween(0, 30)),
            'estado' => fake()->randomElement(array_keys(Postulacion::ESTADOS)),
        ];
    }

    /**
     * Estado: postulacion de la oferta indicada con una fecha coherente,
     * entre la publicacion y el cierre (o hoy, si la oferta sigue abierta).
     */
    public function paraOferta(Oferta $oferta): static
    {
        return $this->state(function () use ($oferta) {
            $desde = $oferta->fecha_publicacion;
            $hasta = $oferta->fecha_cierre->lt(today()) ? $oferta->fecha_cierre : today();

            return [
                'oferta_id' => $oferta->id,
                'fecha_postulacion' => fake()->dateTimeBetween($desde, $hasta)->format('Y-m-d'),
            ];
        });
    }
}
