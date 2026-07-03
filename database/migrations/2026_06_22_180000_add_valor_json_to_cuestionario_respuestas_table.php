<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase F (formularios) — E1.1: soporte de tablas dinámicas.
 *
 * Aditivo: agrega una columna JSON nullable para almacenar filas repetibles
 * (hijos, hermanos, empleos, deudas, tatuajes, referencias, bienes, etc.).
 * Los campos simples siguen usando `valor` (text) sin cambios, por lo que
 * no se afectan datos ni respuestas ya capturadas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cuestionario_respuestas', function (Blueprint $table) {
            // Filas estructuradas de tablas dinámicas. Null para campos simples.
            $table->json('valor_json')->nullable()->after('valor');
        });
    }

    public function down(): void
    {
        Schema::table('cuestionario_respuestas', function (Blueprint $table) {
            $table->dropColumn('valor_json');
        });
    }
};
