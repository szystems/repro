<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase F (formularios) — E1.6: pantalla de instrucciones obligatoria.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cuestionarios', function (Blueprint $table) {
            $table->timestamp('instrucciones_leidas_at')->nullable()->after('bloqueado');
            $table->string('ip_instrucciones', 45)->nullable()->after('instrucciones_leidas_at');
        });
    }

    public function down(): void
    {
        Schema::table('cuestionarios', function (Blueprint $table) {
            $table->dropColumn(['instrucciones_leidas_at', 'ip_instrucciones']);
        });
    }
};
