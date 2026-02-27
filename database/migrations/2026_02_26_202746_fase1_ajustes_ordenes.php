<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 1: Ajustes en tabla ordenes
     * - Renombrar observaciones → observaciones_internas
     * - Agregar tipo_creador (empresa/repro)
     * - Agregar requerimientos_generales
     */
    public function up(): void
    {
        Schema::table('ordenes', function (Blueprint $table) {
            $table->renameColumn('observaciones', 'observaciones_internas');
        });

        Schema::table('ordenes', function (Blueprint $table) {
            $table->enum('tipo_creador', ['empresa', 'repro'])->default('empresa')
                  ->after('creado_por')
                  ->comment('Quién creó la orden: empresa o personal REPRO');

            $table->text('requerimientos_generales')->nullable()
                  ->after('instrucciones_generales')
                  ->comment('Requerimientos del cliente, solo REPRO puede editar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordenes', function (Blueprint $table) {
            $table->dropColumn(['tipo_creador', 'requerimientos_generales']);
        });

        Schema::table('ordenes', function (Blueprint $table) {
            $table->renameColumn('observaciones_internas', 'observaciones');
        });
    }
};
