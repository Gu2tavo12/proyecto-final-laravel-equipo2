<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla de postulaciones de candidatos a una oferta.
     */
    public function up(): void
    {
        // [MIGRACION] Tabla relacionada: postulaciones
        Schema::create('postulaciones', function (Blueprint $table) {
            $table->id();
            // [RELACION] ofertas 1:N postulaciones. cascadeOnDelete: si se elimina
            // una oferta, se eliminan tambien sus postulaciones.
            $table->foreignId('oferta_id')->constrained('ofertas')->cascadeOnDelete();
            $table->string('nombre_candidato', 150);
            $table->string('correo', 150);
            $table->string('carrera', 100);
            $table->string('enlace_cv', 255);
            $table->date('fecha_postulacion');
            $table->enum('estado', ['recibida', 'en_revision', 'aceptada', 'rechazada'])->default('recibida');
            $table->timestamps();

            // Un candidato (correo) solo puede postularse una vez a la misma oferta
            $table->unique(['oferta_id', 'correo']);
        });
    }

    /**
     * Revierte la migracion.
     */
    public function down(): void
    {
        Schema::dropIfExists('postulaciones');
    }
};
