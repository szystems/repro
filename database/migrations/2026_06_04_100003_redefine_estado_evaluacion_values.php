<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Fase 18 - Semana 1: Redefinir estado_evaluacion a 7 valores independientes
     * del proceso de programación/calendario.
     * 
     * NOTA: Según respuesta cliente #5, los registros actuales son de prueba
     * y pueden eliminarse si es necesario. Esta migración intenta mapear pero
     * puede fallar en datos inconsistentes.
     */
    public function up(): void
    {
        // 1. Mapear valores existentes antes de cambiar el ENUM
        // Los estados relacionados con programación ahora están en estado_programacion
        $mapeo = [
            'pendiente'              => 'link_pendiente',
            'contactando'            => 'link_pendiente',  // Contactando es de programación
            'contactado'             => 'link_pendiente',  // Contactado es de programación
            'link_enviado'           => 'link_enviado',
            'confirmado'             => 'pendiente_de_llenar',  // Link enviado pero no llenado
            'programado'             => 'pendiente_de_llenar',  // Programado pero sin formulario
            'en_sede'                => 'pendiente_de_llenar',  // En sede sin formulario
            'docs_pendientes'        => 'pendiente_de_llenar',  // Nombre anterior
            'en_proceso'             => 'en_proceso',
            'resultado_preliminar'   => 'en_revision',
            'completado'             => 'completado',
            'inasistencia'           => 'link_pendiente',  // Reinicia proceso
            'reprogramado'           => 'link_pendiente',  // Reinicia proceso
            'cancelado'              => 'cancelado',       // Se mantiene
            'desistio'               => 'cancelado',       // Mapea a cancelado
        ];

        foreach ($mapeo as $estadoViejo => $estadoNuevo) {
            DB::table('evaluados_orden')
                ->where('estado_evaluacion', $estadoViejo)
                ->update(['estado_evaluacion' => $estadoNuevo]);
        }

        // 2. Redefinir el ENUM con los 7 valores nuevos
        // Primero cambiar a VARCHAR temporal
        DB::statement("ALTER TABLE evaluados_orden MODIFY COLUMN estado_evaluacion VARCHAR(50)");
        
        // Luego redefinir como ENUM con los nuevos valores
        DB::statement("
            ALTER TABLE evaluados_orden 
            MODIFY COLUMN estado_evaluacion ENUM(
                'link_pendiente',
                'link_enviado',
                'pendiente_de_llenar',
                'en_proceso',
                'en_revision',
                'completado',
                'cancelado'
            ) DEFAULT 'link_pendiente'
        ");
    }

    /**
     * Reverse the migrations.
     * 
     * ADVERTENCIA: El down() no restaura los valores originales exactos,
     * solo restablece el ENUM anterior. Los datos mapeados se perderán.
     */
    public function down(): void
    {
        // Restaurar ENUM anterior (sin garantía de recuperar datos exactos)
        DB::statement("ALTER TABLE evaluados_orden MODIFY COLUMN estado_evaluacion VARCHAR(50)");
        
        DB::statement("
            ALTER TABLE evaluados_orden 
            MODIFY COLUMN estado_evaluacion ENUM(
                'pendiente',
                'contactando',
                'contactado',
                'link_enviado',
                'confirmado',
                'programado',
                'en_sede',
                'docs_pendientes',
                'en_proceso',
                'resultado_preliminar',
                'completado',
                'inasistencia',
                'reprogramado',
                'cancelado',
                'desistio'
            ) DEFAULT 'pendiente'
        ");
    }
};
