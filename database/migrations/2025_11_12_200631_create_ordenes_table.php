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
        Schema::create('ordenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->string('codigo_orden')->unique(); // Código único generado automáticamente
            $table->integer('cantidad_evals')->default(1); // Cantidad de evaluaciones solicitadas
            
            // Estados posibles de la orden
            $table->enum('estado', [
                'solicitud',
                'autorizacion', 
                'requisito',
                'programacion',
                'en_proceso',
                'analisis',
                'preliminar',
                'final',
                'entregado',
                'cancelado'
            ])->default('solicitud');
            
            // Usuarios relacionados
            $table->foreignId('creado_por')->constrained('users')->onDelete('cascade'); // Usuario que creó la orden
            
            // Fechas importantes
            $table->date('fecha_solicitud'); // Fecha cuando se solicita
            $table->date('fecha_limite')->nullable(); // Fecha límite para completar
            
            // Información adicional
            $table->text('observaciones')->nullable(); // Observaciones adicionales
            $table->text('instrucciones_generales')->nullable()->comment('Instrucciones generales para todos los evaluados de esta orden');
            $table->enum('prioridad', ['baja', 'normal', 'alta', 'urgente'])->default('normal')->comment('Prioridad de la orden completa');
            $table->json('documentos_adjuntos')->nullable(); // Archivos adjuntos como JSON
            
            $table->timestamps();
            
            // Índices para búsquedas frecuentes
            $table->index(['empresa_id', 'estado']);
            $table->index(['creado_por']);
            $table->index(['fecha_solicitud']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordenes');
    }
};