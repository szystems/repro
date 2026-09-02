<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluados_orden', function (Blueprint $table) {
            if (! Schema::hasColumn('evaluados_orden', 'motivo_reprogramacion')) {
                $table->string('motivo_reprogramacion', 500)->nullable()->after('fecha_programada_original');
            }
        });
    }

    public function down(): void
    {
        Schema::table('evaluados_orden', function (Blueprint $table) {
            if (Schema::hasColumn('evaluados_orden', 'motivo_reprogramacion')) {
                $table->dropColumn('motivo_reprogramacion');
            }
        });
    }
};
