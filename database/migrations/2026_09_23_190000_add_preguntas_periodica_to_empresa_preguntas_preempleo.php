<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresa_preguntas_preempleo', function (Blueprint $table) {
            foreach (['periodica_p1', 'periodica_p2', 'periodica_p3', 'periodica_p4', 'periodica_p5'] as $columna) {
                if (! Schema::hasColumn('empresa_preguntas_preempleo', $columna)) {
                    $table->string($columna, 500)->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('empresa_preguntas_preempleo', function (Blueprint $table) {
            $columnas = array_values(array_filter(
                ['periodica_p1', 'periodica_p2', 'periodica_p3', 'periodica_p4', 'periodica_p5'],
                fn (string $columna) => Schema::hasColumn('empresa_preguntas_preempleo', $columna)
            ));
            if ($columnas !== []) {
                $table->dropColumn($columnas);
            }
        });
    }
};
