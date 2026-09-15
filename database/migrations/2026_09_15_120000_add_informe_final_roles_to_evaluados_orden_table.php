<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluados_orden', function (Blueprint $table) {
            $table->foreignId('informe_final_responsable_id')
                ->nullable()
                ->after('entrevistador_id')
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('informe_final_subido_por')
                ->nullable()
                ->after('resultado_subido_por')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('evaluados_orden', function (Blueprint $table) {
            $table->dropForeign(['informe_final_responsable_id']);
            $table->dropForeign(['informe_final_subido_por']);
            $table->dropColumn(['informe_final_responsable_id', 'informe_final_subido_por']);
        });
    }
};
