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
            if (!Schema::hasColumn('evaluados_orden', 'cuestionario_completado_at')) {
                $table->timestamp('cuestionario_completado_at')->nullable()->after('cuestionario_completado');
            }
            if (!Schema::hasColumn('evaluados_orden', 'ip_acceso')) {
                $table->string('ip_acceso', 45)->nullable()->after('cuestionario_completado_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluados_orden', function (Blueprint $table) {
            $table->dropColumn(['cuestionario_completado_at', 'ip_acceso']);
        });
    }
};
