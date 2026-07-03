<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase F (formularios) — E1.7: snapshot de datos de la orden al iniciar cuestionario.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cuestionarios', function (Blueprint $table) {
            $table->json('datos_precarga_json')->nullable()->after('ip_instrucciones');
        });
    }

    public function down(): void
    {
        Schema::table('cuestionarios', function (Blueprint $table) {
            $table->dropColumn('datos_precarga_json');
        });
    }
};
