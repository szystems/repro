<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Cambiar ordenes.estado de ENUM a VARCHAR(20) para soportar nuevos estados.
     * Migrar valores obsoletos: 'autorizacion' → 'validacion', 'requisito' → 'registrado'.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE ordenes MODIFY COLUMN estado VARCHAR(20) NOT NULL DEFAULT 'solicitud'");

        // Migrar valores obsoletos a nuevos equivalentes
        DB::table('ordenes')->where('estado', 'autorizacion')->update(['estado' => 'validacion']);
        DB::table('ordenes')->where('estado', 'requisito')->update(['estado' => 'registrado']);
    }

    /**
     * Revertir a ENUM original.
     */
    public function down(): void
    {
        // Revertir nombres antes de cambiar tipo
        DB::table('ordenes')->where('estado', 'validacion')->update(['estado' => 'autorizacion']);
        DB::table('ordenes')->where('estado', 'registrado')->update(['estado' => 'requisito']);
        // Limpiar estados nuevos que no existían
        DB::table('ordenes')->where('estado', 'operaciones')->update(['estado' => 'en_proceso']);

        DB::statement("ALTER TABLE ordenes MODIFY COLUMN estado ENUM('solicitud','autorizacion','requisito','programacion','en_proceso','analisis','preliminar','final','entregado','cancelado') NOT NULL DEFAULT 'solicitud'");
    }
};
