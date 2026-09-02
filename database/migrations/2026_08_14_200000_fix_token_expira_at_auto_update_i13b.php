<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * I13b — bugfix schema: elimina `ON UPDATE CURRENT_TIMESTAMP` de token_expira_at.
 *
 * `Blueprint::timestamp('token_expira_at')` en MySQL con `explicit_defaults_for_timestamp = OFF`
 * genera `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`.
 * Cualquier UPDATE al evaluado (p. ej. cambio de estado) sobreescribía token_expira_at con NOW(),
 * caducando el enlace en minutos. Esta migración quita el comportamiento auto y hace la columna
 * nullable (permite invalidar manualmente sin depender de un default MySQL).
 */
return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('evaluados_orden')) {
            return;
        }

        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE evaluados_orden MODIFY token_expira_at TIMESTAMP NULL DEFAULT NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('evaluados_orden')) {
            return;
        }

        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE evaluados_orden MODIFY token_expira_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    }
};
