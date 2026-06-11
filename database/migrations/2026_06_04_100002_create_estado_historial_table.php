<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Fase 18 - Semana 1: Crear tabla para registrar el historial de cambios
     * de los 4 campos de estado independientes (formulario, programación,
     * evaluación y orden).
     */
    public function up(): void
    {
        Schema::create('estado_historial', function (Blueprint $table) {
            $table->id();
            
            // Referencia al evaluado (null si es cambio de estado de Orden)
            $table->foreignId('evaluado_orden_id')
                  ->nullable()
                  ->constrained('evaluados_orden')
                  ->onDelete('cascade');
            
            // Referencia a la orden (para cambios directos en estado de Orden)
            $table->foreignId('orden_id')
                  ->nullable()
                  ->constrained('ordenes')
                  ->onDelete('cascade');
            
            // Campo que cambió: 'estado_formulario', 'estado_programacion', 'estado_evaluacion', 'estado_orden', 'modalidad'
            $table->string('campo', 50);
            
            // Valores del cambio
            $table->string('estado_anterior', 100)->nullable();
            $table->string('estado_nuevo', 100);
            
            // Motivo/observación del cambio (opcional)
            $table->text('observacion')->nullable();
            
            // Usuario que realizó el cambio (null si fue automático)
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            
            $table->timestamps();
            
            // Índices para consultas frecuentes
            $table->index('evaluado_orden_id');
            $table->index('orden_id');
            $table->index('campo');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estado_historial');
    }
};
