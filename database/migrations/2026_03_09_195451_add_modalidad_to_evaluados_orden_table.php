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
            $table->enum('modalidad', ['presencial', 'virtual'])->nullable()->after('sede_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluados_orden', function (Blueprint $table) {
            $table->dropColumn('modalidad');
        });
    }
};
