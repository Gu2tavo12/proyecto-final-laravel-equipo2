<?php

namespace Database\Seeders;

use App\Models\Oferta;
use App\Models\Postulacion;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Catalogo de empresas: debe ir antes que las ofertas
        $this->call(EmpresaSeeder::class);

        // 21 ofertas (>= 15) reciclando las empresas existentes
        Oferta::factory()->count(14)->create();
        Oferta::factory()->count(4)->pasantia()->create();
        Oferta::factory()->count(3)->cerrada()->create();

        // Entre 0 y 5 postulaciones por oferta
        foreach (Oferta::all() as $oferta) {
            // fecha_postulacion entre la publicacion y el cierre de la oferta
            // (y nunca en el futuro, si la oferta sigue abierta)
            $desde = $oferta->fecha_publicacion;
            $hasta = $oferta->fecha_cierre->lt(today()) ? $oferta->fecha_cierre : today();

            Postulacion::factory()
                ->count(fake()->numberBetween(0, 5))
                ->state(fn () => [
                    'oferta_id' => $oferta->id,
                    'fecha_postulacion' => fake()->dateTimeBetween($desde, $hasta)->format('Y-m-d'),
                ])
                ->create();
        }
    }
}
