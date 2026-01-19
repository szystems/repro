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
        Schema::create('formulario_campos', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_formulario', ['preempleo', 'periodica', 'especifica', 'socioeconomico']);
            $table->string('seccion', 50);
            $table->string('campo', 100);
            $table->string('etiqueta', 255);
            $table->enum('tipo_campo', ['text', 'select', 'textarea', 'date', 'number', 'boolean', 'file'])->default('text');
            $table->json('opciones')->nullable(); // Para campos select, radio, checkbox
            $table->text('placeholder')->nullable();
            $table->text('ayuda')->nullable(); // Texto de ayuda para el usuario
            $table->boolean('requerido')->default(false);
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->string('validaciones', 500)->nullable(); // Reglas de validación Laravel
            $table->timestamps();
            
            $table->unique(['tipo_formulario', 'seccion', 'campo']);
            $table->index(['tipo_formulario', 'seccion', 'orden']);
            $table->index(['activo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formulario_campos');
    }
};
