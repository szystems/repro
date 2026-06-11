<?php

namespace App\Console\Commands;

use App\Models\EvaluadoOrden;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Fase 18: Auto-transiciones temporales del estado_formulario.
 *
 * Reglas (del plan Fase 18 / respuesta cliente #7):
 *  - link_enviado  +24h sin abrir → pendiente_de_llenar
 *  - cualquier estado no completado  +30 días (token_expira_at < now()) → vencido
 *
 * La referencia de tiempo se deriva de token_expira_at:
 *  - token_expira_at se fija en now()+30d al crear o rehabilitar el evaluado.
 *  - "24h transcurridas" ≡ token_expira_at < now()+29d
 *  - "30 días transcurridos" ≡ token_expira_at < now()
 */
class AutoTransicionarEstadosFormulario extends Command
{
    protected $signature   = 'formulario:auto-transiciones {--dry-run : Solo muestra qué cambiaría sin aplicar}';
    protected $description = 'Aplica auto-transiciones temporales de estado_formulario (Fase 18)';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $vencidos = 0;
        $pendientes = 0;

        // ── 1. link_enviado + 24h → pendiente_de_llenar ──────────────────────
        // token_expira_at BETWEEN now() AND now()+29d:
        //   - < now()+29d ≡ generado hace > 24h
        //   - >= now()    ≡ no expirado todavía (los expirados van directo a vencido en step 2)
        $candidatosPendiente = EvaluadoOrden::where('estado_formulario', 'link_enviado')
            ->where('token_expira_at', '<', now()->addDays(29))
            ->where('token_expira_at', '>=', now())
            ->get();

        foreach ($candidatosPendiente as $evaluado) {
            if ($dryRun) {
                $this->line("[DRY-RUN] #{$evaluado->id} {$evaluado->nombre}: link_enviado → pendiente_de_llenar");
                $pendientes++;
                continue;
            }

            if ($evaluado->cambiarEstadoFormulario('pendiente_de_llenar')) {
                $pendientes++;
                Log::info('AutoTransicion: link_enviado → pendiente_de_llenar', [
                    'evaluado_id' => $evaluado->id,
                    'token_expira_at' => $evaluado->token_expira_at,
                ]);
            }
        }

        // ── 2. Cualquier estado incompleto + 30 días → vencido ───────────────
        // token_expira_at < now()  ≡  han pasado 30 días desde que se generó el token
        $candidatosVencido = EvaluadoOrden::whereIn('estado_formulario', ['link_enviado', 'link_pendiente', 'pendiente_de_llenar'])
            ->where('token_expira_at', '<', now())
            ->get();

        foreach ($candidatosVencido as $evaluado) {
            if ($dryRun) {
                $this->line("[DRY-RUN] #{$evaluado->id} {$evaluado->nombre}: {$evaluado->estado_formulario} → vencido");
                $vencidos++;
                continue;
            }

            if ($evaluado->cambiarEstadoFormulario('vencido')) {
                $vencidos++;
                Log::info('AutoTransicion: estado_formulario → vencido', [
                    'evaluado_id'     => $evaluado->id,
                    'estado_anterior' => $evaluado->getOriginal('estado_formulario'),
                    'token_expira_at' => $evaluado->token_expira_at,
                ]);
            }
        }

        $this->info("Auto-transiciones completadas: {$pendientes} pendiente_de_llenar, {$vencidos} vencido" . ($dryRun ? ' [DRY-RUN]' : ''));

        return self::SUCCESS;
    }
}
