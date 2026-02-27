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
        Schema::create('documento_evaluados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluado_orden_id')->constrained('evaluados_orden')->cascadeOnDelete();
            $table->enum('tipo_documento', [
                'antecedentes_penales',
                'antecedentes_policiacos',
                'cv',
                'constancia_estudios',
                'licencia_auto',
                'licencia_moto',
                'dpi_archivo',
                'pasaporte',
                'carta_laboral',
                'foto_tatuaje',
                'autorizacion_firmada',
                'otro',
            ]);
            $table->string('nombre_original'); // nombre original del archivo subido
            $table->string('ruta_archivo');    // path en storage
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('tamano')->default(0); // bytes
            $table->enum('subido_por_tipo', ['empresa', 'repro', 'evaluado'])->default('evaluado');
            $table->foreignId('subido_por_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('estado_verificacion', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente');
            $table->foreignId('verificado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verificado_at')->nullable();
            $table->text('notas_verificacion')->nullable();
            $table->timestamps();

            $table->index(['evaluado_orden_id', 'tipo_documento']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documento_evaluados');
    }
};
