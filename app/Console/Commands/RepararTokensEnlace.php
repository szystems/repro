<?php

namespace App\Console\Commands;

use App\Models\Config;
use App\Models\EvaluadoOrden;
use Illuminate\Console\Command;

class RepararTokensEnlace extends Command
{
    /** Enlaces con delta creado→expira menor a este umbral se consideran «rotos» (I13b). */
    private const HORAS_UMBRAL_ROTURA = 24;

    protected $signature = 'repro:reparar-tokens-enlace
                            {--ids= : IDs de evaluados separados por coma}
                            {--dry-run : Solo listar candidatos sin modificar}';

    protected $description = 'Extiende token_expira_at de enlaces con vigencia anormalmente corta (I13b)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $ids = collect(explode(',', (string) $this->option('ids')))
            ->map(fn ($id) => trim($id))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0);

        $query = EvaluadoOrden::query()
            ->where('cuestionario_completado', false)
            ->whereNotNull('token_expira_at')
            ->whereNotNull('token_unico');

        if ($ids->isNotEmpty()) {
            $query->whereIn('id', $ids->all());
        } else {
            $query->where('token_expira_at', '<', now()->addDay());
        }

        $candidatos = $query->orderBy('id')->get();

        if ($ids->isEmpty()) {
            $candidatos = $candidatos->filter(function (EvaluadoOrden $ev): bool {
                if (! $ev->created_at || ! $ev->token_expira_at) {
                    return false;
                }

                if ($ev->token_expira_at->isPast()) {
                    return true;
                }

                return $ev->created_at->diffInHours($ev->token_expira_at) < self::HORAS_UMBRAL_ROTURA;
            })->values();
        }

        if ($candidatos->isEmpty()) {
            $this->info('No hay enlaces que reparar.');

            return self::SUCCESS;
        }

        $this->info('Candidatos: '.$candidatos->count().($dryRun ? ' (dry-run)' : ''));

        $reparados = 0;

        foreach ($candidatos as $evaluado) {
            $expiraAnterior = $evaluado->token_expira_at;
            $nuevaExpira = EvaluadoOrden::calcularExpiracionToken();
            $diasConfig = Config::diasVigenciaTokenEnlace();

            $this->line(sprintf(
                '#%d %s %s | expira=%s | estado=%s | → +%d días (%s)',
                $evaluado->id,
                $evaluado->nombre,
                $evaluado->apellidos ?? '',
                $expiraAnterior?->format('Y-m-d H:i'),
                $evaluado->estado_formulario,
                $diasConfig,
                $nuevaExpira->format('Y-m-d H:i')
            ));

            if ($dryRun) {
                continue;
            }

            $evaluado->update([
                'token_expira_at' => $nuevaExpira,
                'estado_formulario' => $evaluado->estado_formulario === 'vencido'
                    ? ($evaluado->cuestionario && (int) ($evaluado->cuestionario->progreso_porcentaje ?? 0) > 0
                        ? 'pendiente_de_llenar'
                        : 'link_pendiente')
                    : $evaluado->estado_formulario,
            ]);

            $reparados++;
        }

        if (! $dryRun) {
            $this->info("Reparados: {$reparados}");
        }

        return self::SUCCESS;
    }
}
