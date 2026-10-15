<?php

namespace App\Support;

use App\Models\Config;
use App\Models\EvaluadoOrden;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Auto-transiciones de estado_formulario (Fase 18).
 * El cron en iPage suele estar apagado: también se llama al listar/ver órdenes.
 *
 * Reglas:
 *  - link_enviado +24h → pendiente_de_llenar
 *  - enlace ya vencido (token_expira_at < now) → vencido
 */
class FormularioAutoTransiciones
{
    public const CACHE_ON_ACCESS = 'formulario.auto_transiciones.on_access';

    /**
     * @return array{pendientes: int, vencidos: int}
     */
    public static function aplicar(bool $dryRun = false): array
    {
        $pendientes = 0;
        $vencidos = 0;
        $limite24h = self::limiteEnlaceConMasDe24Horas();
        $ahora = now();

        $candidatosPendiente = EvaluadoOrden::query()
            ->where('estado_formulario', 'link_enviado')
            ->where('token_expira_at', '<', $limite24h)
            ->where('token_expira_at', '>=', $ahora)
            ->get();

        foreach ($candidatosPendiente as $evaluado) {
            if ($dryRun) {
                $pendientes++;
                continue;
            }
            if ($evaluado->cambiarEstadoFormulario('pendiente_de_llenar', 'Cambio automático del sistema', true)) {
                $pendientes++;
                Log::info('AutoTransicion: link_enviado → pendiente_de_llenar', [
                    'evaluado_id' => $evaluado->id,
                    'token_expira_at' => $evaluado->token_expira_at,
                ]);
            }
        }

        $candidatosVencido = EvaluadoOrden::query()
            ->whereIn('estado_formulario', ['link_enviado', 'link_pendiente', 'pendiente_de_llenar'])
            ->where('token_expira_at', '<', $ahora)
            ->get();

        foreach ($candidatosVencido as $evaluado) {
            if ($dryRun) {
                $vencidos++;
                continue;
            }
            if ($evaluado->cambiarEstadoFormulario('vencido', 'Cambio automático del sistema', true)) {
                $vencidos++;
                Log::info('AutoTransicion: estado_formulario → vencido', [
                    'evaluado_id' => $evaluado->id,
                    'estado_anterior' => $evaluado->getOriginal('estado_formulario'),
                    'token_expira_at' => $evaluado->token_expira_at,
                ]);
            }
        }

        return compact('pendientes', 'vencidos');
    }

    /**
     * Un enlace nuevo vence en N días (hoy 15). Lleva más de 24 h cuando a
     * token_expira_at le queda menos de N−1 días. Con el umbral fijo de 29 días
     * (pensado para enlaces de 30) un enlace de 15 días ya cumplía al crearse.
     */
    public static function limiteEnlaceConMasDe24Horas(): Carbon
    {
        return now()->copy()->addDays(Config::diasVigenciaTokenEnlace() - 1);
    }

    /** Una pasada cada 5 minutos al entrar a listados/fichas (fallback si no hay cron). */
    public static function aplicarAlAcceder(): void
    {
        try {
            if (! Cache::add(self::CACHE_ON_ACCESS, 1, now()->addMinutes(5))) {
                return;
            }
            self::aplicar();
        } catch (\Throwable $e) {
            Log::warning('FormularioAutoTransiciones on-access: '.$e->getMessage());
        }
    }
}
