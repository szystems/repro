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
        Schema::create('evaluados_orden', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_id')->constrained('ordenes')->onDelete('cascade');
            
            // Datos personales del evaluado
            $table->string('nombre'); // Nombre del evaluado
            $table->string('apellidos', 150)->nullable()->comment('Apellidos del evaluado');
            $table->string('email'); // Email para envío de token
            $table->string('telefono')->nullable(); // Teléfono de contacto
            
            // Identificación del evaluado
            $table->string('dpi', 13); // DPI guatemalteco (13 dígitos) - SIN UNIQUE para permitir múltiples evaluaciones
            $table->enum('tipo_documento', ['dpi', 'pasaporte', 'cedula'])->default('dpi');
            
            // Configuración específica del servicio para este evaluado
            $table->enum('tipo_servicio', ['poligrafo', 'vsa', 'socioeconomico'])->comment('Tipo de servicio específico para este evaluado');
            $table->enum('tipo_formulario', ['preempleo', 'periodica', 'especifica'])->comment('Tipo de formulario específico para este evaluado');
            
            // Asignación de recursos
            $table->foreignId('poligrafista_id')->nullable()->constrained('users')->onDelete('set null')->comment('Polígrafo asignado específicamente a este evaluado');
            $table->string('puesto_evaluar')->nullable(); // Puesto para el cual se evalúa
            
            // Fechas y programación
            $table->date('fecha_programada')->nullable()->comment('Fecha programada para la evaluación de este evaluado');
            $table->datetime('fecha_realizada')->nullable()->comment('Fecha y hora cuando se realizó la evaluación');
            
            // Estado y progreso
            $table->enum('estado_evaluacion', [
                'pendiente',     // Recién creado, sin contactar
                'contactado',    // Se hizo contacto inicial
                'programado',    // Fecha confirmada
                'en_proceso',    // Evaluación en curso
                'completado',    // Evaluación terminada
                'cancelado',     // Cancelado por alguna razón
                'reprogramado'   // Necesita nueva fecha
            ])->default('pendiente')->comment('Estado específico de la evaluación de este evaluado');
            
            // Acceso sin login y cuestionario
            $table->string('token_unico', 64)->unique(); // Token único para acceso sin login
            $table->timestamp('token_expira_at'); // Expiración del token (30 días)
            $table->boolean('cuestionario_completado')->default(false);
            $table->timestamp('completado_at')->nullable(); // Cuándo completó el cuestionario
            
            // Resultado y notas
            $table->enum('resultado', [
                'pendiente',
                'aprobado', 
                'no_aprobado',
                'inconcluso'
            ])->nullable()->comment('Resultado final de la evaluación');
            
            // Datos técnicos y seguimiento
            $table->text('firma_digital')->nullable(); // Firma digital del evaluado
            $table->string('ip_completado')->nullable(); // IP desde donde completó
            $table->text('observaciones')->nullable(); // Observaciones específicas del evaluado
            $table->text('notas_poligrafo')->nullable()->comment('Notas específicas del polígrafo para este evaluado');
            
            $table->timestamps();
            
            // Índices para búsquedas frecuentes
            $table->index(['orden_id']);
            $table->index(['dpi']); // Importante para consultar historial
            $table->index(['email']);
            $table->index(['token_unico']);
            $table->index(['cuestionario_completado']);
            $table->index(['estado_evaluacion']);
            $table->index(['poligrafista_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluados_orden');
    }
};