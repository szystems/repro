<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint E — Confidencialidad reclutadores (§3.10) + WhatsApp PROCESO VIRTUAL (§3.8).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordenes', function (Blueprint $table) {
            if (! Schema::hasColumn('ordenes', 'reclutador_id')) {
                $table->foreignId('reclutador_id')->nullable()->after('creado_por')
                    ->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('ordenes', 'confidencial')) {
                $table->boolean('confidencial')->default(false)->after('reclutador_id');
            }
        });

        Schema::table('empresas', function (Blueprint $table) {
            if (! Schema::hasColumn('empresas', 'modo_visibilidad_reclutadores')) {
                $table->string('modo_visibilidad_reclutadores', 20)
                    ->default('compartido')
                    ->after('notas');
            }
        });

        // Corregir WhatsApp erróneo 77677811 → 77637811 (cliente ago-2026 §3.8)
        DB::table('sedes')
            ->where(function ($q) {
                $q->where('whatsapp', 'like', '%77677811%')
                    ->orWhere(function ($q2) {
                        $q2->where('nombre', 'like', '%PROCESO VIRTUAL%')
                            ->where(function ($q3) {
                                $q3->whereNull('whatsapp')
                                    ->orWhere('whatsapp', 'like', '%77677811%')
                                    ->orWhere('whatsapp', 'like', '%776778%');
                            });
                    });
            })
            ->update(['whatsapp' => '50277637811']);
    }

    public function down(): void
    {
        Schema::table('ordenes', function (Blueprint $table) {
            if (Schema::hasColumn('ordenes', 'reclutador_id')) {
                $table->dropForeign(['reclutador_id']);
                $table->dropColumn('reclutador_id');
            }
            if (Schema::hasColumn('ordenes', 'confidencial')) {
                $table->dropColumn('confidencial');
            }
        });

        Schema::table('empresas', function (Blueprint $table) {
            if (Schema::hasColumn('empresas', 'modo_visibilidad_reclutadores')) {
                $table->dropColumn('modo_visibilidad_reclutadores');
            }
        });
    }
};
