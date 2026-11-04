<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea el catalogo de empresas que publican ofertas.
     */
    public function up(): void
    {
        // [MIGRACION] Tabla catalogo: empresas
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150)->unique();
            $table->string('rubro', 100);
            $table->string('correo_contacto', 150)->unique();
            $table->string('telefono', 9); // Formato 0000-0000
            $table->timestamps();
        });
    }

    /**
     * Revierte la migracion.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
