<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Q-A1: quién entrevistó. No recicla Programó (poligrafista_id) ni Encargado (responsable_id).
     */
    public function up(): void
    {
        Schema::table('evaluados_orden', function (Blueprint $table) {
            $table->foreignId('entrevistador_id')
                ->nullable()
                ->after('responsable_id')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('evaluados_orden', function (Blueprint $table) {
            $table->dropForeign(['entrevistador_id']);
            $table->dropColumn('entrevistador_id');
        });
    }
};
