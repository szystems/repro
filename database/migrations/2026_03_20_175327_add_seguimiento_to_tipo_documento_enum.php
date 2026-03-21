<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE documento_evaluados MODIFY COLUMN tipo_documento ENUM('antecedentes_penales','antecedentes_policiacos','cv','constancia_estudios','licencia_auto','licencia_moto','dpi_archivo','pasaporte','carta_laboral','foto_tatuaje','autorizacion_firmada','otro','seguimiento') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE documento_evaluados MODIFY COLUMN tipo_documento ENUM('antecedentes_penales','antecedentes_policiacos','cv','constancia_estudios','licencia_auto','licencia_moto','dpi_archivo','pasaporte','carta_laboral','foto_tatuaje','autorizacion_firmada','otro') NOT NULL");
    }
};
