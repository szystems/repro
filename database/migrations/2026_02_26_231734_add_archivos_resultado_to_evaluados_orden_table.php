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
            $table->string('archivo_resultado_preliminar')->nullable()->after('resultado');
            $table->string('archivo_resultado_final')->nullable()->after('archivo_resultado_preliminar');
            $table->timestamp('resultado_preliminar_at')->nullable()->after('archivo_resultado_final');
            $table->timestamp('resultado_final_at')->nullable()->after('resultado_preliminar_at');
            $table->foreignId('resultado_subido_por')->nullable()->after('resultado_final_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluados_orden', function (Blueprint $table) {
            $table->dropForeign(['resultado_subido_por']);
            $table->dropColumn([
                'archivo_resultado_preliminar',
                'archivo_resultado_final',
                'resultado_preliminar_at',
                'resultado_final_at',
                'resultado_subido_por',
            ]);
        });
    }
};
