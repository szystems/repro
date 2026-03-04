<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cambiar estado_evaluacion de ENUM a VARCHAR(20) para soportar nuevos estados.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE evaluados_orden MODIFY COLUMN estado_evaluacion VARCHAR(20) NOT NULL DEFAULT 'pendiente'");
    }

    /**
     * Revertir a ENUM original (solo contiene los valores originales).
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE evaluados_orden MODIFY COLUMN estado_evaluacion ENUM('pendiente','contactado','programado','en_proceso','completado','cancelado','reprogramado') NOT NULL DEFAULT 'pendiente'");
    }
};
