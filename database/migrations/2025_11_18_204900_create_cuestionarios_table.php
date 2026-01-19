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
        Schema::create('cuestionarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evaluado_orden_id');
            $table->enum('tipo_formulario', ['preempleo', 'periodica', 'especifica', 'socioeconomico']);
            $table->integer('seccion_actual')->default(1);
            $table->integer('total_secciones');
            $table->decimal('progreso_porcentaje', 5, 2)->default(0);
            $table->boolean('completado')->default(false);
            $table->boolean('bloqueado')->default(false);
            $table->text('firma_digital')->nullable();
            $table->string('ip_completado', 45)->nullable();
            $table->timestamp('completado_at')->nullable();
            $table->text('observaciones_repro')->nullable(); // Para que REPRO agregue notas
            $table->timestamps();
            
            $table->foreign('evaluado_orden_id')->references('id')->on('evaluados_orden')->onDelete('cascade');
            $table->index(['tipo_formulario', 'completado']);
            $table->index('evaluado_orden_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuestionarios');
    }
};
