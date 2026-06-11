<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fase 18 — Corrección: estado_evaluacion debe reflejar la etapa técnica de la evaluación
     * física (polígrafo/VSA/socioeconómico), NO el estado del formulario en línea.
     *
     * Valores anteriores (incorrectos — eran estados de formulario):
     *   link_pendiente, link_enviado, pendiente_de_llenar, en_proceso, en_revision, completado, cancelado
     *
     * Valores correctos según PDF del cliente:
     *   pendiente_de_evaluacion → en_proceso → en_revision → resultado_preliminar → informe_final_enviado
     *   cancelado / desistio (solo desde pendiente_de_evaluacion)
     *
     * La base de datos de producción contiene solo datos de prueba (respuesta cliente #5),
     * por lo que se mapean todos los registros a 'pendiente_de_evaluacion' y se redefine el ENUM.
     */
    public function up(): void
    {
        // 1. Ampliar a VARCHAR para poder asignar los nuevos valores
        DB::statement("ALTER TABLE evaluados_orden MODIFY COLUMN estado_evaluacion VARCHAR(50)");

        // 2. Mapear valores antiguos al nuevo modelo
        $mapeo = [
            'link_pendiente'      => 'pendiente_de_evaluacion',
            'link_enviado'        => 'pendiente_de_evaluacion',
            'pendiente_de_llenar' => 'pendiente_de_evaluacion',
            'completado'          => 'informe_final_enviado',
            // en_proceso, en_revision, cancelado se mantienen igual
        ];

        foreach ($mapeo as $estadoViejo => $estadoNuevo) {
            DB::table('evaluados_orden')
                ->where('estado_evaluacion', $estadoViejo)
                ->update(['estado_evaluacion' => $estadoNuevo]);
        }

        // 3. Redefinir como ENUM con los 7 valores correctos
        DB::statement("
            ALTER TABLE evaluados_orden
            MODIFY COLUMN estado_evaluacion ENUM(
                'pendiente_de_evaluacion',
                'en_proceso',
                'en_revision',
                'resultado_preliminar',
                'informe_final_enviado',
                'cancelado',
                'desistio'
            ) DEFAULT 'pendiente_de_evaluacion'
        ");
    }

    public function down(): void
    {
        // Revertir al ENUM anterior (incorrecto)
        $mapeo = [
            'pendiente_de_evaluacion' => 'link_pendiente',
            'en_proceso'              => 'en_proceso',
            'en_revision'             => 'en_revision',
            'resultado_preliminar'    => 'en_revision',
            'informe_final_enviado'   => 'completado',
            'cancelado'               => 'cancelado',
            'desistio'                => 'cancelado',
        ];

        foreach ($mapeo as $estadoViejo => $estadoNuevo) {
            DB::table('evaluados_orden')
                ->where('estado_evaluacion', $estadoViejo)
                ->update(['estado_evaluacion' => $estadoNuevo]);
        }

        DB::statement("ALTER TABLE evaluados_orden MODIFY COLUMN estado_evaluacion VARCHAR(50)");

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
};
