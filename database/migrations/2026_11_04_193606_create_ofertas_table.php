<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla principal de ofertas de empleo y pasantias.
     */
    public function up(): void
    {
        // [MIGRACION] Tabla principal: ofertas
        Schema::create('ofertas', function (Blueprint $table) {
            $table->id();
            // [RELACION] empresas 1:N ofertas. restrictOnDelete: no se puede borrar
            // una empresa que todavia tiene ofertas registradas.
            $table->foreignId('empresa_id')->constrained('empresas')->restrictOnDelete();
            $table->string('titulo', 150);
            $table->text('descripcion');
            $table->enum('modalidad', ['presencial', 'remota', 'hibrida']);
            $table->enum('tipo', ['empleo', 'pasantia']);
            $table->decimal('salario_minimo', 10, 2)->nullable();
            $table->decimal('salario_maximo', 10, 2)->nullable();
            $table->date('fecha_publicacion');
            $table->date('fecha_cierre');
            $table->unsignedSmallInteger('vacantes');
            $table->string('imagen')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Revierte la migracion.
     */
    public function down(): void
    {
        Schema::dropIfExists('ofertas');
    }
};
