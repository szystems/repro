<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 1: Clasificación de resultados según cotización REPRO
     *
     * Polígrafo/VSA:
     *   aprobado          → Aprobado / Sin Observaciones (verde)
     *   aprobado_con_obs  → Aprobado / Con Observación Leve (amarillo)
     *   aprobado_excepcion → Aprobado con Excepción (amarillo)
     *   no_aprobado       → No Aprobado / Indicación de Mentira (rojo)
     *
     * Socioeconómico:
     *   tipo_a            → Tipo A (verde)
     *   a_condicionado    → A - Condicionado (amarillo)
     *   tipo_b            → Tipo B (naranja)
     *   tipo_c            → Tipo C (rojo)
     *
     * Comunes: pendiente, inconcluso
     */
    public function up(): void
    {
        // MySQL requires ALTER to change ENUM values
        DB::statement("ALTER TABLE evaluados_orden MODIFY COLUMN resultado ENUM(
            'pendiente',
            'aprobado',
            'aprobado_con_obs',
            'aprobado_excepcion',
            'no_aprobado',
            'inconcluso',
            'tipo_a',
            'a_condicionado',
            'tipo_b',
            'tipo_c'
        ) NULL DEFAULT NULL COMMENT 'Resultado final con clasificación REPRO'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE evaluados_orden MODIFY COLUMN resultado ENUM(
            'pendiente',
            'aprobado',
            'no_aprobado',
            'inconcluso'
        ) NULL DEFAULT NULL COMMENT 'Resultado final de la evaluación'");
    }
};
