<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Fase 18 - Semana 1: Simplificar estados de Orden a 4 valores.
     * El estado detallado ahora está en los evaluados individuales.
     */
    public function up(): void
    {
        // 1. Mapear valores existentes
        $mapeo = [
            'solicitud'     => 'orden_recibida',
            'autorizacion'  => 'orden_recibida',
            'requisito'     => 'orden_recibida',
            'programacion'  => 'en_proceso',
            'en_proceso'    => 'en_proceso',
            'preliminar'    => 'en_proceso',
            'final'         => 'en_proceso',
            'entregado'     => 'entregado',
            'cancelado'     => 'cancelado',
        ];

        foreach ($mapeo as $estadoViejo => $estadoNuevo) {
            DB::table('ordenes')
                ->where('estado', $estadoViejo)
                ->update(['estado' => $estadoNuevo]);
        }

        // 2. Redefinir el ENUM con los 4 valores nuevos
        DB::statement("ALTER TABLE ordenes MODIFY COLUMN estado VARCHAR(50)");
        
        DB::statement("
            ALTER TABLE ordenes 
            MODIFY COLUMN estado ENUM(
                'orden_recibida',
                'en_proceso',
                'entregado',
                'cancelado'
            ) DEFAULT 'orden_recibida'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurar ENUM anterior
        DB::statement("ALTER TABLE ordenes MODIFY COLUMN estado VARCHAR(50)");
        
        DB::statement("
            ALTER TABLE ordenes 
            MODIFY COLUMN estado ENUM(
                'solicitud',
                'autorizacion',
                'requisito',
                'programacion',
                'en_proceso',
                'preliminar',
                'final',
                'entregado',
                'cancelado'
            ) DEFAULT 'solicitud'
        ");
    }
};
