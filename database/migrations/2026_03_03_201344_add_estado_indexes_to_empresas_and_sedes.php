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
        Schema::table('empresas', function (Blueprint $table) {
            $table->index('estado', 'empresas_estado_index');
        });

        Schema::table('sedes', function (Blueprint $table) {
            $table->index('estado', 'sedes_estado_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropIndex('empresas_estado_index');
        });

        Schema::table('sedes', function (Blueprint $table) {
            $table->dropIndex('sedes_estado_index');
        });
    }
};
