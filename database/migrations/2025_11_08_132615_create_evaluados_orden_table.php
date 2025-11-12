<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabla para evaluados que NO son usuarios del sistema.
     * Cada evaluado está vinculado a una orden específica y accede
     * mediante un token único temporal sin necesidad de autenticación.
     */
    public function up(): void
    {
        Schema::create('evaluados_orden', function (Blueprint $table) {
            $table->id();
            
            // Relación con orden (requerida)
            $table->foreignId('orden_id')
                ->nullable() // Temporal hasta crear tabla ordenes
                ->comment('ID de la orden a la que pertenece este evaluado');
            
            // Datos personales del evaluado
            $table->string('nombre', 150)->comment('Nombre completo del evaluado');
            $table->string('email', 150)->comment('Email para recibir link del cuestionario');
            $table->string('telefono', 20)->nullable()->comment('Teléfono de contacto');
            $table->string('celular', 20)->nullable()->comment('Celular de contacto');
            
            // Identificación única por DPI (documento guatemalteco)
            $table->string('dpi', 13)->comment('Documento Personal de Identificación (único)');
            $table->enum('tipo_documento', ['DPI', 'Pasaporte', 'Licencia'])
                ->default('DPI')
                ->comment('Tipo de documento de identificación');
            
            // Sistema de acceso por token único
            $table->string('token_unico', 64)->unique()->comment('Token único para acceso sin login');
            $table->timestamp('token_expira_at')->nullable()->comment('Fecha de expiración del token (30 días)');
            $table->timestamp('token_usado_at')->nullable()->comment('Cuándo se usó el token por primera vez');
            
            // Estado del cuestionario
            $table->boolean('cuestionario_completado')->default(false)->comment('Si completó el cuestionario');
            $table->timestamp('completado_at')->nullable()->comment('Fecha de completado del cuestionario');
            $table->text('firma_digital')->nullable()->comment('Firma digital del evaluado (base64)');
            
            // Auditoría de acceso
            $table->string('ip_completado', 45)->nullable()->comment('IP desde donde completó el cuestionario');
            $table->string('user_agent', 255)->nullable()->comment('Navegador usado');
            $table->integer('intentos_acceso')->default(0)->comment('Número de veces que accedió al formulario');
            
            // Campos adicionales opcionales
            $table->text('notas')->nullable()->comment('Notas internas de Repro (no visibles para evaluado)');
            $table->boolean('notificado')->default(false)->comment('Si se le envió el email con el link');
            $table->timestamp('notificado_at')->nullable()->comment('Cuándo se envió el email');
            
            $table->timestamps();
            $table->softDeletes(); // Por si necesitan "eliminar" sin borrar físicamente
            
            // Índices para búsquedas frecuentes
            $table->index('dpi', 'idx_evaluados_dpi'); // Buscar historial por DPI
            $table->index('email', 'idx_evaluados_email');
            $table->index(['orden_id', 'cuestionario_completado'], 'idx_orden_estado');
            $table->index('token_expira_at', 'idx_token_expira');
            
            // Comentario de tabla
            $table->comment('Evaluados vinculados a órdenes sin cuenta de usuario');
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
