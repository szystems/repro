<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Fase 18 - Semana 1: Agregar campo estado_programacion para separar
     * la lógica de programación/calendario del estado de evaluación.
     */
    public function up(): void
    {
        Schema::table('evaluados_orden', function (Blueprint $table) {
            // 8 valores sin "Asistió" según respuesta cliente #3
            $table->enum('estado_programacion', [
                'contactando',
                'contactado',
                'programado',
                'proceso_realizado',
                'reprogramado',
                'inasistencia',
                'desistio',
                'cancelado'
            ])->default('contactando')->after('estado_formulario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluados_orden', function (Blueprint $table) {
            $table->dropColumn('estado_programacion');
        });
    }
};
