<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluados_orden', function (Blueprint $table) {
            if (!Schema::hasColumn('evaluados_orden', 'motivo_hecho_evaluacion')) {
                $table->text('motivo_hecho_evaluacion')->nullable()->after('puesto_evaluar');
            }
        });

        Schema::table('cuestionarios', function (Blueprint $table) {
            if (!Schema::hasColumn('cuestionarios', 'acepta_infornet')) {
                $table->boolean('acepta_infornet')->default(false)->after('ip_terminos');
            }
            if (!Schema::hasColumn('cuestionarios', 'acepta_infornet_at')) {
                $table->timestamp('acepta_infornet_at')->nullable()->after('acepta_infornet');
            }
            if (!Schema::hasColumn('cuestionarios', 'texto_autorizacion_html')) {
                $table->longText('texto_autorizacion_html')->nullable()->after('acepta_infornet_at');
            }
            if (!Schema::hasColumn('cuestionarios', 'texto_infornet_html')) {
                $table->longText('texto_infornet_html')->nullable()->after('texto_autorizacion_html');
            }
        });
    }

    public function down(): void
    {
        Schema::table('evaluados_orden', function (Blueprint $table) {
            if (Schema::hasColumn('evaluados_orden', 'motivo_hecho_evaluacion')) {
                $table->dropColumn('motivo_hecho_evaluacion');
            }
        });

        Schema::table('cuestionarios', function (Blueprint $table) {
            $cols = ['acepta_infornet', 'acepta_infornet_at', 'texto_autorizacion_html', 'texto_infornet_html'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('cuestionarios', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
