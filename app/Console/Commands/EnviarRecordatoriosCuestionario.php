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
                try {
                    if (!$evaluado->email) {
                        $this->warn("   ⚠️ {$evaluado->nombre} {$evaluado->apellidos}: Sin email");
                        continue;
                    }

                    // Verificar si ya se envió recordatorio hoy (a menos que se fuerce)
                    if (!$this->option('forzar') && $this->yaSeEnvioRecordatorioHoy($evaluado)) {
                        $this->line("   ⏭️ {$evaluado->nombre}: Ya recibió recordatorio hoy");
                        continue;
                    }

                    Mail::to($evaluado->email)
                        ->send(new RecordatorioCuestionarioMail($evaluado, $dias));

                    // Registrar que se envió el recordatorio
                    $evaluado->update([
                        'notificado_at' => now(),
                    ]);

                    $this->info("   ✅ {$evaluado->nombre} {$evaluado->apellidos}: Enviado a {$evaluado->email}");
                    $enviados++;

                    Log::info("Recordatorio enviado", [
                        'evaluado_id' => $evaluado->id,
                        'email' => $evaluado->email,
                        'dias_restantes' => $dias,
                    ]);

                } catch (\Exception $e) {
                    $this->error("   ❌ {$evaluado->nombre}: Error - " . $e->getMessage());
                    $errores++;

                    Log::error("Error enviando recordatorio", [
                        'evaluado_id' => $evaluado->id,
                        'error' => $e->getMessage(),
                    ]);
                }
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

        return EvaluadoOrden::query()
            ->where('cuestionario_completado', false)
            ->whereNotNull('token_unico')
            ->whereNotNull('email')
            ->whereBetween('token_expira_at', [$fechaObjetivo, $fechaObjetivoFin])
            ->with('orden.empresa')
            ->get();
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
