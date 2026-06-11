<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fase 18 - Semana 1 fix: Convertir estado_formulario de VARCHAR(20)
     * a ENUM con los 5 valores correctos.
     *
     * El campo fue creado como varchar(20) en migraciones anteriores.
     * El valor 'formulario_completado_y_recibido' tiene 32 caracteres y
     * no cabe en VARCHAR(20), por lo que los tests y el código fallaban.
     */
    public function up(): void
    {
        // Paso 1: ampliar la columna para que quepan los nuevos valores largos
        DB::statement("ALTER TABLE evaluados_orden MODIFY COLUMN estado_formulario VARCHAR(50) NOT NULL DEFAULT 'link_pendiente'");

        // Paso 2: mapear valores legacy al nuevo vocabulario
        $mapeo = [
            'pendiente'    => 'link_pendiente',
            'link_enviado' => 'link_enviado',
            'en_progreso'  => 'pendiente_de_llenar',
            'completado'   => 'formulario_completado_y_recibido',
            'expirado'     => 'vencido',
        ];

        foreach ($mapeo as $viejo => $nuevo) {
            DB::table('evaluados_orden')
                ->where('estado_formulario', $viejo)
                ->update(['estado_formulario' => $nuevo]);
        }

        // Paso 3: convertir a ENUM con los 5 valores de Fase 18
        DB::statement("
            ALTER TABLE evaluados_orden
            MODIFY COLUMN estado_formulario ENUM(
                'link_pendiente',
                'link_enviado',
                'pendiente_de_llenar',
                'formulario_completado_y_recibido',
                'vencido'
            ) NOT NULL DEFAULT 'link_pendiente'
        ");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE evaluados_orden MODIFY COLUMN estado_formulario VARCHAR(20) NOT NULL DEFAULT 'pendiente'");
    }
};
