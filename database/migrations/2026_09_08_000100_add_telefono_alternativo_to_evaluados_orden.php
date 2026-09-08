<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('evaluados_orden', 'telefono_alternativo')) {
            Schema::table('evaluados_orden', function (Blueprint $table) {
                $table->string('telefono_alternativo', 20)->nullable()->after('telefono');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('evaluados_orden', 'telefono_alternativo')) {
            Schema::table('evaluados_orden', function (Blueprint $table) {
                $table->dropColumn('telefono_alternativo');
            });
        }
    }
};
