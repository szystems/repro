<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 4 — Calendario y Agenda.
 *
 * 1. Cambia `fecha_programada` de date → datetime (hora de inicio de la cita).
 * 2. Agrega `fecha_hora_fin` (datetime) para hora de finalización.
 * 3. Agrega índice compuesto para consultas de calendario.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluados_orden', function (Blueprint $table) {
            // Cambiar fecha_programada de date a datetime (hora inicio)
            $table->dateTime('fecha_programada')->nullable()
                ->comment('Fecha y hora de INICIO de la cita programada')
                ->change();

            // Agregar hora fin
            $table->dateTime('fecha_hora_fin')->nullable()
                ->after('fecha_programada')
                ->comment('Fecha y hora de FIN de la cita programada');

            // Índice compuesto para queries de calendario (rango de fechas + sede + poligrafista)
            $table->index(['fecha_programada', 'fecha_hora_fin'], 'idx_calendario_rango');
            $table->index(['sede_id', 'fecha_programada'], 'idx_calendario_sede');
        });
    }

    public function down(): void
    {
        Schema::table('evaluados_orden', function (Blueprint $table) {
            $table->dropIndex('idx_calendario_sede');
            $table->dropIndex('idx_calendario_rango');

            $table->dropColumn('fecha_hora_fin');

            // Revertir a date
            $table->date('fecha_programada')->nullable()
                ->comment('Fecha programada para la evaluación de este evaluado')
                ->change();
        });
    }
};
