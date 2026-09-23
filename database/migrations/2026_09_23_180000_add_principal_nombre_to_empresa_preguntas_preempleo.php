<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresa_preguntas_preempleo', function (Blueprint $table) {
            if (! Schema::hasColumn('empresa_preguntas_preempleo', 'principal_nombre')) {
                $table->string('principal_nombre', 100)->nullable()->after('empresa_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('empresa_preguntas_preempleo', function (Blueprint $table) {
            if (Schema::hasColumn('empresa_preguntas_preempleo', 'principal_nombre')) {
                $table->dropColumn('principal_nombre');
            }
        });
    }
};
