<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('evaluados_orden', function (Blueprint $table) {
            // Todos los campos necesarios ya existen en create_evaluados_orden_table:
            // - token_unico (string, 64, unique)
            // - token_expira_at (timestamp)
            // - cuestionario_completado (boolean, default false)
            // - completado_at (timestamp, nullable) [equivale a cuestionario_completado_at]
            // - ip_completado (string, nullable) [equivale a ip_acceso]
            
            // Solo agregamos índices adicionales si no existen
            $table->index(['cuestionario_completado', 'token_expira_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluados_orden', function (Blueprint $table) {
            // Solo eliminamos los índices que agregamos
            $table->dropIndex(['cuestionario_completado', 'token_expira_at']);
            // No eliminamos campos porque ya existen en create_evaluados_orden_table
        });
    }
};
