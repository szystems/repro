<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluados_orden', function (Blueprint $table) {
            $table->boolean('informe_preliminar_editado_manual')
                ->default(false)
                ->after('texto_informe_preliminar');
        });
    }

    public function down(): void
    {
        Schema::table('evaluados_orden', function (Blueprint $table) {
            $table->dropColumn('informe_preliminar_editado_manual');
        });
    }
};
