<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Aviso cuando el correo deja de salir (tope Resend u otro rechazo SMTP).
 * No bloquea el portal: la campana y la orden siguen. Solo avisa a REPRO.
 */
class CorreoEnvioSupport
{
    public const NIVEL_AVISO = 'warning';

    public const NIVEL_CORTE = 'danger';

    public static function registrarEnvio(): void
    {
        if (! self::activa() || self::omitirMailerDePrueba()) {
            return;
        }

        $enviados = (int) Cache::increment(self::claveContador());
        Cache::put(self::claveContador(), $enviados, self::expiraMedianoche());

        $limite = self::limiteDiario();
        $avisoDesde = self::avisoDesde();

        if ($limite > 0 && $enviados >= $limite) {
            self::guardarAlerta(self::NIVEL_CORTE, self::mensajeCorte($limite));

            return;
        }

        // Solo aviso persistente al llegar al tope; acercarse (p. ej. 90/100) no llena pantalla todo el día.
    }

    public static function registrarFallo(Throwable $e, ?string $contexto = null): void
    {
        Log::error('Correo no enviado', [
            'contexto' => $contexto,
            'error' => $e->getMessage(),
        ]);

        if (! self::activa()) {
            return;
        }

        $limite = self::limiteDiario();

        if (self::esErrorTope($e)) {
            self::guardarAlerta(self::NIVEL_CORTE, self::mensajeCorte($limite));
        }

        // Fallos puntuales (timeout, etc.): solo log. El flash de la acción avisa en esa pantalla;
        // no dejamos banner global todo el día si el correo sí vuelve a salir.
    }

    /**
     * @return array{nivel: string, mensaje: string}|null
     */
    public static function alertaActiva(): ?array
    {
        if (! self::activa()) {
            return null;
        }

        $alerta = Cache::get(self::claveAlerta());

        return is_array($alerta) && isset($alerta['mensaje'], $alerta['nivel'])
            ? $alerta
            : null;
    }

    public static function esErrorTope(Throwable $e): bool
    {
        $texto = strtolower($e->getMessage());

        foreach ([
            '429',
            'too many',
            'rate limit',
            'daily limit',
            'daily email',
            'sending limit',
            'quota',
            'maximum number of emails',
            'limite diario',
            'límite diario',
        ] as $marca) {
            if (str_contains($texto, $marca)) {
                return true;
            }
        }

        return false;
    }

    public static function enviadosHoy(): int
    {
        return (int) Cache::get(self::claveContador(), 0);
    }

    public static function mensajeFlashFallo(): string
    {
        return 'El cambio quedó en el portal, pero el correo no se pudo enviar.';
    }

    public static function activa(): bool
    {
        return (bool) config('mail.alerta.activa', true);
    }

    public static function limpiar(): void
    {
        Cache::forget(self::claveContador());
        Cache::forget(self::claveAlerta());
    }

    /** Quita el banner amarillo/rojo global (p. ej. aviso obsoleto tras deploy). */
    public static function olvidarAlertaPersistente(): void
    {
        Cache::forget(self::claveAlerta());
    }

    private static function guardarAlerta(string $nivel, string $mensaje): void
    {
        Cache::put(self::claveAlerta(), [
            'nivel' => $nivel,
            'mensaje' => $mensaje,
        ], self::expiraMedianoche());
    }

    private static function mensajeAviso(int $enviados, int $limite): string
    {
        return "Hoy ya salieron {$enviados} correos automáticos (tope del servicio: {$limite}/día). Si dejan de llegar, es el límite, no un fallo del portal. Se reinicia mañana.";
    }

    private static function mensajeCorte(int $limite): string
    {
        $reanuda = self::reanudaEnGuatemala()->format('H:i');

        return "Los correos automáticos se detuvieron: el servicio de correo llegó a su límite ({$limite}/día). Se reanudan a las {$reanuda} (hora de Guatemala). Los avisos en el portal sí quedan.";
    }

    private static function limiteDiario(): int
    {
        return max(0, (int) config('mail.alerta.limite_diario', 100));
    }

    private static function avisoDesde(): int
    {
        return max(0, (int) config('mail.alerta.aviso_desde', 90));
    }

    private static function omitirMailerDePrueba(): bool
    {
        return in_array(config('mail.default'), ['array', 'log'], true);
    }

    /**
     * Resend reinicia la cuota a las 00:00 UTC (18:00 en Guatemala).
     * El día del portal (medianoche en Guatemala) parte ese ciclo a la mitad
     * y el aviso decía «van 14 de 100» cuando el proveedor ya no aceptaba más.
     */
    private static function ahoraProveedor(): Carbon
    {
        return now()->timezone('UTC');
    }

    private static function reanudaEnGuatemala(): Carbon
    {
        return self::ahoraProveedor()->copy()->addDay()->startOfDay()->timezone('America/Guatemala');
    }

    private static function claveContador(): string
    {
        return 'correo.enviados.'.self::ahoraProveedor()->toDateString();
    }

    private static function claveAlerta(): string
    {
        return 'correo.alerta.'.self::ahoraProveedor()->toDateString();
    }

    private static function expiraMedianoche(): \DateTimeInterface
    {
        return self::ahoraProveedor()->copy()->endOfDay();
    }
}
