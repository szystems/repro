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
        Schema::create('auditoria_estados', function (Blueprint $table) {
            $table->id();
            $table->string('entidad_tipo', 50);
            $table->unsignedBigInteger('entidad_id');
            $table->string('campo', 50);
            $table->string('estado_anterior', 50)->nullable();
            $table->string('estado_nuevo', 50);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['entidad_tipo', 'entidad_id'], 'auditoria_estados_entidad_index');
            $table->index('user_id', 'auditoria_estados_user_index');
            $table->index('created_at', 'auditoria_estados_created_index');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auditoria_estados');
    }
};
