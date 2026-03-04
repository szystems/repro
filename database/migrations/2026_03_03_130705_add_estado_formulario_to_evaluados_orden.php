<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Agregar columna estado_formulario a evaluados_orden.
     *
     * Valores: pendiente, link_enviado, en_progreso, completado, expirado
     * Se migran datos existentes desde cuestionario_completado + token_expira_at.
     */
    public function up(): void
    {
        // Columna nueva solo si no existe (manejo de migración parcial)
        if (!Schema::hasColumn('evaluados_orden', 'estado_formulario')) {
            Schema::table('evaluados_orden', function (Blueprint $table) {
                $table->string('estado_formulario', 20)->default('pendiente')->after('estado_evaluacion');
                $table->index('estado_formulario');
            });
        }

        // Migrar datos existentes
        // 1. Completados
        DB::table('evaluados_orden')
            ->where('cuestionario_completado', true)
            ->update(['estado_formulario' => 'completado']);

        // 2. Expirados (no completados y token expirado)
        DB::table('evaluados_orden')
            ->where('cuestionario_completado', false)
            ->whereNotNull('token_expira_at')
            ->where('token_expira_at', '<', now())
            ->update(['estado_formulario' => 'expirado']);

        // 3. Los demás permanecen como 'pendiente' (default de la columna)
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluados_orden', function (Blueprint $table) {
            $table->dropIndex(['estado_formulario']);
            $table->dropColumn('estado_formulario');
        });
    }
};
