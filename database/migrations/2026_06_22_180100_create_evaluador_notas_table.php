<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase F (formularios) — E1.8: espacios internos del evaluador.
 *
 * Notas/observaciones/análisis que redacta ÚNICAMENTE el personal de REPRO
 * (rol REPRO/ADMIN), separadas de las respuestas del candidato. La empresa
 * NO crea ni edita estos registros.
 *
 * Se asocian al evaluado y a una sección/campo lógico para poder incorporarse
 * luego al informe final (tablas/narrativa editable por el evaluador).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluador_notas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evaluado_orden_id');
            $table->string('seccion', 60);            // p.ej. 'laboral', 'economica', 'salud', 'judicial'
            $table->string('campo', 100)->nullable(); // sub-bloque opcional dentro de la sección
            $table->longText('contenido')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // autor (usuario REPRO/ADMIN)
            $table->timestamps();

            $table->foreign('evaluado_orden_id')->references('id')->on('evaluados_orden')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->unique(['evaluado_orden_id', 'seccion', 'campo']);
            $table->index(['evaluado_orden_id', 'seccion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluador_notas');
    }
};
