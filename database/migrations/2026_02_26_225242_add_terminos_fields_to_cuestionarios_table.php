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
        Schema::table('cuestionarios', function (Blueprint $table) {
            $table->boolean('acepta_terminos')->default(false)->after('bloqueado');
            $table->timestamp('acepta_terminos_at')->nullable()->after('acepta_terminos');
            $table->text('firma_autorizacion')->nullable()->after('acepta_terminos_at');
            $table->string('ip_terminos')->nullable()->after('firma_autorizacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cuestionarios', function (Blueprint $table) {
            $table->dropColumn(['acepta_terminos', 'acepta_terminos_at', 'firma_autorizacion', 'ip_terminos']);
        });
    }
};
