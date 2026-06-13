<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordenes', function (Blueprint $table) {
            $table->boolean('archivada')->default(false)->after('resultados_visibles_empresa');
            $table->timestamp('archivada_at')->nullable()->after('archivada');
            $table->foreignId('archivada_por')->nullable()->after('archivada_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ordenes', function (Blueprint $table) {
            $table->dropForeign(['archivada_por']);
            $table->dropColumn(['archivada', 'archivada_at', 'archivada_por']);
        });
    }
};
