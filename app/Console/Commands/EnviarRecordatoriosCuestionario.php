<?php

namespace App\Console\Commands;

use App\Mail\RecordatorioCuestionarioMail;
use App\Models\EvaluadoOrden;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Comando para enviar recordatorios automáticos a evaluados
 * cuyos cuestionarios están próximos a expirar.
 * 
 * Se recomienda programar este comando para ejecutarse diariamente.
 */
class EnviarRecordatoriosCuestionario extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notificaciones:recordatorios 
                            {--dias=3,1 : Días antes de expiración para enviar recordatorios (separados por coma)}
                            {--despues-alta=1 : Días después del alta del evaluado (0 desactiva)}
                            {--forzar : Enviar incluso si ya se envió un recordatorio hoy}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía recordatorios a evaluados con cuestionarios próximos a expirar';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $diasRecordatorio = collect(explode(',', $this->option('dias')))
            ->map(fn($d) => (int) trim($d))
            ->filter(fn($d) => $d > 0)
            ->values();

        $this->info("🔔 Iniciando envío de recordatorios...");
        $this->info("   Días configurados: " . $diasRecordatorio->implode(', '));

        $enviados = 0;
        $errores = 0;

        $diasDespuesAlta = (int) $this->option('despues-alta');
        if ($diasDespuesAlta > 0) {
            $this->line("");
            $this->info("📅 Procesando evaluados dados de alta hace {$diasDespuesAlta} día(s)...");

            $evaluadosAlta = $this->obtenerEvaluadosPorAlta($diasDespuesAlta);
            if ($evaluadosAlta->isEmpty()) {
                $this->line("   No hay evaluados pendientes para este período.");
            } else {
                $this->line("   Encontrados: {$evaluadosAlta->count()} evaluados");
                foreach ($evaluadosAlta as $evaluado) {
                    [$enviados, $errores] = $this->enviarA($evaluado, $enviados, $errores, null, true);
                }
            }
        }

        foreach ($diasRecordatorio as $dias) {
            $this->line("");
            $this->info("📅 Procesando evaluados que expiran en {$dias} día(s)...");

            $evaluados = $this->obtenerEvaluadosPorExpirar($dias);

            if ($evaluados->isEmpty()) {
                $this->line("   No hay evaluados pendientes para este período.");
                continue;
            }

            $this->line("   Encontrados: {$evaluados->count()} evaluados");

            foreach ($evaluados as $evaluado) {
                [$enviados, $errores] = $this->enviarA($evaluado, $enviados, $errores, $dias, false);
            }
        }

        $this->line("");
        $this->info("═══════════════════════════════════════");
        $this->info("📊 Resumen:");
        $this->info("   ✅ Enviados: {$enviados}");
        if ($errores > 0) {
            $this->error("   ❌ Errores: {$errores}");
        }
        $this->info("═══════════════════════════════════════");

        return $errores > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * Obtener evaluados cuyo token expira en X días y no han completado el cuestionario.
     */
    private function obtenerEvaluadosPorExpirar(int $dias): \Illuminate\Database\Eloquent\Collection
    {
        $fechaObjetivo = now()->addDays($dias)->startOfDay();
        $fechaObjetivoFin = now()->addDays($dias)->endOfDay();

        return $this->queryPendientesRecordatorio()
            ->whereBetween('token_expira_at', [$fechaObjetivo, $fechaObjetivoFin])
            ->get();
    }

    /**
     * Evaluados dados de alta hace N días, formulario incompleto y enlace vigente.
     */
    private function obtenerEvaluadosPorAlta(int $dias): \Illuminate\Database\Eloquent\Collection
    {
        $desde = now()->subDays($dias)->startOfDay();
        $hasta = now()->subDays($dias)->endOfDay();

        return $this->queryPendientesRecordatorio()
            ->where('token_expira_at', '>', now())
            ->whereBetween('created_at', [$desde, $hasta])
            ->get();
    }

    private function queryPendientesRecordatorio()
    {
        return EvaluadoOrden::query()
            ->where('cuestionario_completado', false)
            ->whereNotNull('token_unico')
            ->whereNotNull('email')
            ->whereNotIn('estado_evaluacion', ['cancelado', 'desistio'])
            ->whereHas('orden', function ($q) {
                $q->where('archivada', false)
                    ->where('estado', '!=', 'cancelado');
            })
            ->with('orden.empresa');
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function enviarA(EvaluadoOrden $evaluado, int $enviados, int $errores, ?int $diasRestantes, bool $recordatorioAlta): array
    {
        try {
            if (! $evaluado->email) {
                $this->warn("   ⚠️ {$evaluado->nombre} {$evaluado->apellidos}: Sin email");

                return [$enviados, $errores];
            }

            if (! $this->option('forzar') && $this->yaSeEnvioRecordatorioHoy($evaluado)) {
                $this->line("   ⏭️ {$evaluado->nombre}: Ya recibió recordatorio hoy");

                return [$enviados, $errores];
            }

            $dias = $diasRestantes;
            if ($dias === null) {
                $expira = $evaluado->token_expira_at;
                $dias = $expira
                    ? max(0, (int) now()->startOfDay()->diffInDays($expira->copy()->startOfDay(), false))
                    : 0;
            }

            Mail::to($evaluado->email)
                ->send(new RecordatorioCuestionarioMail($evaluado, $dias, $recordatorioAlta));

            $evaluado->update([
                'notificado_at' => now(),
            ]);

            $this->info("   ✅ {$evaluado->nombre} {$evaluado->apellidos}: Enviado a {$evaluado->email}");
            $enviados++;

            Log::info('Recordatorio enviado', [
                'evaluado_id' => $evaluado->id,
                'email' => $evaluado->email,
                'dias_restantes' => $dias,
                'recordatorio_alta' => $recordatorioAlta,
            ]);
        } catch (\Exception $e) {
            $this->error("   ❌ {$evaluado->nombre}: Error - ".$e->getMessage());
            $errores++;

            Log::error('Error enviando recordatorio', [
                'evaluado_id' => $evaluado->id,
                'error' => $e->getMessage(),
            ]);
        }

        return [$enviados, $errores];
    }

    /**
     * Verificar si ya se envió un recordatorio hoy para este evaluado.
     */
    private function yaSeEnvioRecordatorioHoy(EvaluadoOrden $evaluado): bool
    {
        if (!$evaluado->notificado_at) {
            return false;
        }

        return $evaluado->notificado_at->isToday();
    }
}
