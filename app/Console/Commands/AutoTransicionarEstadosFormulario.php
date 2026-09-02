<?php

namespace App\Console\Commands;

use App\Support\FormularioAutoTransiciones;
use Illuminate\Console\Command;

/**
 * Fase 18: Auto-transiciones temporales del estado_formulario.
 * La regla vive en FormularioAutoTransiciones (también se aplica on-access).
 */
class AutoTransicionarEstadosFormulario extends Command
{
    protected $signature = 'formulario:auto-transiciones {--dry-run : Solo muestra qué cambiaría sin aplicar}';

    protected $description = 'Aplica auto-transiciones temporales de estado_formulario (Fase 18)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $resultado = FormularioAutoTransiciones::aplicar($dryRun);

        $this->info(
            "Auto-transiciones completadas: {$resultado['pendientes']} pendiente_de_llenar, {$resultado['vencidos']} vencido"
            .($dryRun ? ' [DRY-RUN]' : '')
        );

        return self::SUCCESS;
    }
}
