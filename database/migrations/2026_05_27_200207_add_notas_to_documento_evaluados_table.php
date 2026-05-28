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
        Schema::table('documento_evaluados', function (Blueprint $table) {
            $table->string('notas', 500)->nullable()->after('notas_verificacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documento_evaluados', function (Blueprint $table) {
            $table->dropColumn('notas');
        });
    }
};
