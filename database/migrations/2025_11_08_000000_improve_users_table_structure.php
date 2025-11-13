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
        Schema::table('users', function (Blueprint $table) {
            // Agregar índices para mejorar performance
            $table->index('role_as');
            $table->index('empresa_id');
            $table->index('estado');
            
            // Agregar campos específicos para evaluados
            $table->string('documento_identidad', 50)->nullable()->after('email');
            $table->enum('tipo_documento', ['DPI', 'Pasaporte', 'Licencia'])->nullable()->after('documento_identidad');
            
            // Campo para tracking de cuestionarios
            $table->boolean('cuestionario_completado')->default(false)->after('permisos');
            $table->timestamp('cuestionario_completado_at')->nullable()->after('cuestionario_completado');
            
            // Agregar índice para búsquedas por documento
            $table->index('documento_identidad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role_as']);
            $table->dropIndex(['empresa_id']);
            $table->dropIndex(['estado']);
            $table->dropIndex(['documento_identidad']);
            
            $table->dropColumn([
                'documento_identidad',
                'tipo_documento',
                'cuestionario_completado',
                'cuestionario_completado_at'
            ]);
        });
    }
};
