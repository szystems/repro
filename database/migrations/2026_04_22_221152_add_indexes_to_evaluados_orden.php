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
        Schema::table('evaluados_orden', function (Blueprint $table) {
            $table->index('created_at', 'evaluados_orden_created_at_index');
            $table->index(['orden_id', 'created_at'], 'evaluados_orden_orden_created_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluados_orden', function (Blueprint $table) {
            $table->dropIndex('evaluados_orden_created_at_index');
            $table->dropIndex('evaluados_orden_orden_created_index');
        });
    }
};
