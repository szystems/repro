<?php

namespace App\Support;

use App\Models\Config;
use App\Models\EvaluadoOrden;
use App\Models\Sede;

/**
 * Datos y elección de plantilla del correo de cita (WA Stephany 7-sep 17:19).
 * Socioeconómico: un solo bloque según modalidad, no ambos.
 */
class CitaProgramadaContenido
{
    public const PLANTILLA_VSA = 'vsa';

    public const PLANTILLA_POLIGRAFO = 'poligrafo';

    public const PLANTILLA_SOCIO = 'socioeconomico';

    public static function plantilla(EvaluadoOrden $evaluado): string
    {
        return match ($evaluado->tipo_servicio) {
            'vsa' => self::PLANTILLA_VSA,
            'socioeconomico' => self::PLANTILLA_SOCIO,
            default => self::PLANTILLA_POLIGRAFO,
        };
    }

    public static function esVirtual(EvaluadoOrden $evaluado): bool
    {
        return strtolower((string) ($evaluado->modalidad ?? '')) === 'virtual';
    }

    public static function mostrarSedeYDireccion(EvaluadoOrden $evaluado): bool
    {
        if (self::esVirtual($evaluado)) {
            return false;
        }

        return $evaluado->sede !== null;
    }

    public static function tituloServicio(EvaluadoOrden $evaluado): string
    {
        return match (self::plantilla($evaluado)) {
            self::PLANTILLA_VSA => 'Prueba VSA - Análisis de Estrés de Voz',
            self::PLANTILLA_SOCIO => 'Entrevista de Seguridad - Estudio Socioeconómico',
            default => 'Prueba de Polígrafo',
        };
    }

    public static function etiquetaModalidad(EvaluadoOrden $evaluado): string
    {
        return match (strtolower((string) ($evaluado->modalidad ?? ''))) {
            'virtual' => 'Virtual',
            'presencial' => 'Presencial',
            default => $evaluado->modalidad ? ucfirst((string) $evaluado->modalidad) : 'N/A',
        };
    }

    public static function urlCuestionario(EvaluadoOrden $evaluado): ?string
    {
        $token = trim((string) ($evaluado->token_unico ?? ''));
        if ($token === '') {
            return null;
        }

        return route('cuestionario.mostrar', ['token' => $token]);
    }

    public static function whatsappUrl(EvaluadoOrden $evaluado): ?string
    {
        $numero = trim((string) ($evaluado->sede?->whatsapp ?? ''));
        if ($numero === '') {
            try {
                $otra = Sede::activas()
                    ->whereNotNull('whatsapp')
                    ->where('whatsapp', '!=', '')
                    ->orderBy('nombre')
                    ->value('whatsapp');
                $numero = trim((string) ($otra ?? ''));
            } catch (\Throwable) {
                $numero = '';
            }
        }

        if ($numero !== '') {
            $digitos = preg_replace('/\D+/', '', $numero) ?? '';

            return $digitos !== '' ? 'https://wa.me/'.$digitos : null;
        }

        try {
            $link = trim((string) (Config::first()?->wapp_link ?? ''));
        } catch (\Throwable) {
            $link = '';
        }

        return $link !== '' ? $link : null;
    }
}
