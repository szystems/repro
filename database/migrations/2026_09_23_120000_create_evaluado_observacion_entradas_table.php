<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluado_observacion_entradas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluado_orden_id')
                ->constrained('evaluados_orden')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('texto');
            $table->boolean('sin_fecha_original')->default(false);
            $table->timestamps();

            $table->index(['evaluado_orden_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluado_observacion_entradas');
    }
};
