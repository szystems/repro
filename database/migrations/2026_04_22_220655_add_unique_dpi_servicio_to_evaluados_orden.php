<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * H-02: prevenir evaluados duplicados (mismo DPI + tipo_servicio)
     * dentro de una misma orden. Garantiza la regla de negocio a nivel BD.
     */
    public function up(): void
    {
        Schema::table('evaluados_orden', function (Blueprint $table) {
            $table->unique(
                ['orden_id', 'dpi', 'tipo_servicio'],
                'evaluados_orden_unique_dpi_servicio'
            );
        });
    }

    public function down(): void
    {
        Schema::table('evaluados_orden', function (Blueprint $table) {
            $table->dropUnique('evaluados_orden_unique_dpi_servicio');
        });
    }
};
