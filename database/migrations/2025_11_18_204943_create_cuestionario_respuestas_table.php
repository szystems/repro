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
        Schema::create('cuestionario_respuestas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cuestionario_id');
            $table->string('seccion', 50); // 'datos_personales', 'familia', 'laboral', etc.
            $table->string('campo', 100); // 'nombre', 'dpi', 'telefono', etc.
            $table->text('valor')->nullable();
            $table->enum('tipo_campo', ['text', 'select', 'textarea', 'date', 'number', 'boolean', 'file'])->default('text');
            $table->boolean('requerido')->default(false);
            $table->json('metadata')->nullable(); // Para datos adicionales como validaciones, opciones, etc.
            $table->timestamps();
            
            $table->foreign('cuestionario_id')->references('id')->on('cuestionarios')->onDelete('cascade');
            $table->unique(['cuestionario_id', 'seccion', 'campo']);
            $table->index(['cuestionario_id', 'seccion']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuestionario_respuestas');
    }
};
