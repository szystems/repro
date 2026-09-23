<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresa_preguntas_preempleo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->unique()->constrained('empresas')->cascadeOnDelete();
            $table->string('p1', 500)->nullable();
            $table->string('p2', 500)->nullable();
            $table->string('p3', 500)->nullable();
            $table->string('p4', 500)->nullable();
            $table->string('p5', 500)->nullable();
            $table->string('puesto_nombre', 100)->nullable();
            $table->string('puesto_p1', 500)->nullable();
            $table->string('puesto_p2', 500)->nullable();
            $table->string('puesto_p3', 500)->nullable();
            $table->string('puesto_p4', 500)->nullable();
            $table->string('puesto_p5', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresa_preguntas_preempleo');
    }
};
