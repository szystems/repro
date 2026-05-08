<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Migrar ordenes.estado al flujo de 8 etapas (A6).
     *
     * Mapeo de estados obsoletos → nuevos:
     *   validacion  → autorizacion
     *   registrado  → autorizacion
     *   operaciones → en_proceso
     *   analisis    → en_proceso
     */
    public function up(): void
    {
        DB::table('ordenes')->where('estado', 'validacion')->update(['estado' => 'autorizacion']);
        DB::table('ordenes')->where('estado', 'registrado')->update(['estado' => 'autorizacion']);
        DB::table('ordenes')->where('estado', 'operaciones')->update(['estado' => 'en_proceso']);
        DB::table('ordenes')->where('estado', 'analisis')->update(['estado' => 'en_proceso']);
    }

    /**
     * Revertir al mapeo anterior.
     */
    public function down(): void
    {
        DB::table('ordenes')->where('estado', 'autorizacion')->update(['estado' => 'validacion']);
        DB::table('ordenes')->where('estado', 'requisito')->update(['estado' => 'registrado']);
    }
};
