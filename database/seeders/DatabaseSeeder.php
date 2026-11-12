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
        // Semilla fija: cada migrate:fresh --seed genera los mismos nombres y correos.
        fake()->seed(20261111);

        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Catalogo de empresas: debe ir antes que las ofertas
        $this->call(EmpresaSeeder::class);

        // 8 ofertas fijas para la demostracion (7+6+5+4+3+2+1+0 = 28 postulaciones)
        $this->call(DemostracionSeeder::class);

        // 12 ofertas complementarias (20 en total, >= 15) reciclando las empresas
        $complementarias = Oferta::factory()->count(8)->create()
            ->concat(Oferta::factory()->count(2)->pasantia()->create())
            ->concat(Oferta::factory()->count(2)->cerrada()->create());

        // Exactamente 1 postulacion por oferta complementaria, con estados rotativos:
        // asi el ranking del reporte no cambia entre ejecuciones (40 postulaciones,
        // 10 por estado) y las fechas quedan entre la publicacion y el cierre.
        $estados = array_keys(Postulacion::ESTADOS);

        foreach ($complementarias->values() as $i => $oferta) {
            Postulacion::factory()
                ->paraOferta($oferta)
                ->create(['estado' => $estados[$i % count($estados)]]);
        }
    }
}
