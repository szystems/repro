<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // Nivel de acceso que define el role_as del usuario al asignar este rol:
            // 1 = empresa, 2 = repro, 3 = admin
            $table->tinyInteger('level')->unsigned()->default(1)->after('name')
                ->comment('Nivel de acceso: 1=empresa, 2=repro, 3=admin');
        });

        // Asignar level a los roles del sistema
        DB::table('roles')->where('name', 'admin')->update(['level' => 3]);
        DB::table('roles')->where('name', 'repro')->update(['level' => 2]);
        DB::table('roles')->where('name', 'empresa')->update(['level' => 1]);
        DB::table('roles')->where('name', 'evaluado')->update(['level' => 0]);

        // Roles custom: inferir level desde el nombre (best-effort)
        DB::table('roles')
            ->whereNotIn('name', ['admin', 'repro', 'empresa', 'evaluado'])
            ->where(function ($q) {
                $q->where('name', 'like', '%admin%')
                  ->orWhere('name', 'like', '%administr%');
            })
            ->update(['level' => 3]);

        DB::table('roles')
            ->whereNotIn('name', ['admin', 'repro', 'empresa', 'evaluado'])
            ->where(function ($q) {
                $q->where('name', 'like', '%repro%');
            })
            ->update(['level' => 2]);
        // Los demás custom quedan en level=1 (empresa) por el default
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('level');
        });
    }
};
