<?php

namespace App\Support;

use Carbon\Carbon;
use InvalidArgumentException;

/**
 * Rango «Fechas laboradas» — mes/año en formulario (H5), texto unificado al guardar (mm/yyyy al …).
 */
class FechasLaboradasCampo
{
    public const SUFIJO_INICIO = '_inicio';

    public const SUFIJO_FIN = '_fin';

    public const SUFIJO_ACTUAL = '_actual';

    public const SUFIJO_MES = '_mes';

    public const SUFIJO_ANIO = '_anio';

    /** Selector uniforme en todos los navegadores (Safari/iOS no soporta input[type=month]). */
    public const MESES = [
        '01' => 'Enero',
        '02' => 'Febrero',
        '03' => 'Marzo',
        '04' => 'Abril',
        '05' => 'Mayo',
        '06' => 'Junio',
        '07' => 'Julio',
        '08' => 'Agosto',
        '09' => 'Septiembre',
        '10' => 'Octubre',
        '11' => 'Noviembre',
        '12' => 'Diciembre',
    ];

    private const ANIOS_ATRAS = 70;

    /** @return list<string> años seleccionables, del más reciente al más antiguo */
    public static function anios(): array
    {
        $actual = (int) Carbon::now()->format('Y');
        $anios = [];

        for ($anio = $actual; $anio >= $actual - self::ANIOS_ATRAS; $anio--) {
            $anios[] = (string) $anio;
        }

        return $anios;
    }

    /**
     * Nombres de los campos auxiliares que el formulario envía para un rango.
     *
     * @return list<string>
     */
    public static function clavesAuxiliares(string $key): array
    {
        $claves = [$key.self::SUFIJO_ACTUAL];

        foreach ([self::SUFIJO_INICIO, self::SUFIJO_FIN] as $extremo) {
            $claves[] = $key.$extremo;
            $claves[] = $key.$extremo.self::SUFIJO_MES;
            $claves[] = $key.$extremo.self::SUFIJO_ANIO;
        }

        return $claves;
    }

    /**
     * Periodo «YYYY-MM» a partir del campo directo o de los selectores mes/año.
     *
     * @param  array<string, mixed>  $fila
     */
    public static function resolverPeriodo(array $fila, string $base): string
    {
        $directo = trim((string) ($fila[$base] ?? ''));

        if ($directo !== '') {
            // Conserva el formato recibido: mes/año o fecha completa de versiones anteriores.
            if (preg_match('/^\d{4}-\d{2}(?:-\d{2})?$/', $directo)) {
                return $directo;
            }

            $normalizado = self::toInputMonth($directo);
            if ($normalizado !== '') {
                return $normalizado;
            }
        }

        $mes = trim((string) ($fila[$base.self::SUFIJO_MES] ?? ''));
        $anio = trim((string) ($fila[$base.self::SUFIJO_ANIO] ?? ''));

        if ($mes === '' || $anio === '' || ! ctype_digit($mes) || ! ctype_digit($anio)) {
            return '';
        }

        $mesInt = (int) $mes;
        $anioInt = (int) $anio;

        if ($mesInt < 1 || $mesInt > 12 || $anioInt < 1900 || $anioInt > 2200) {
            return '';
        }

        return sprintf('%04d-%02d', $anioInt, $mesInt);
    }

    /**
     * Divide «YYYY-MM» en las partes que consumen los selectores.
     *
     * @return array{mes: string, anio: string}
     */
    public static function partes(string $periodo): array
    {
        if (preg_match('/^(\d{4})-(\d{2})$/', $periodo, $m)) {
            return ['mes' => $m[2], 'anio' => $m[1]];
        }

        return ['mes' => '', 'anio' => ''];
    }

    /**
     * @return array{inicio: string, fin: string, actual: bool}
     */
    public static function parse(?string $valor): array
    {
        $valor = trim((string) $valor);
        $result = ['inicio' => '', 'fin' => '', 'actual' => false];

        if ($valor === '') {
            return $result;
        }

        if (preg_match('/\s+(?:al|a)\s+/iu', $valor, $match, PREG_OFFSET_CAPTURE)) {
            $parteInicio = trim(substr($valor, 0, $match[0][1]));
            $parteFin = trim(substr($valor, $match[0][1] + strlen($match[0][0])));
        } else {
            $parteInicio = $valor;
            $parteFin = '';
        }

        $result['inicio'] = self::toInputMonth($parteInicio);

        if ($parteFin === '') {
            return $result;
        }

        if (strcasecmp($parteFin, 'Actual') === 0) {
            $result['actual'] = true;

            return $result;
        }

        $result['fin'] = self::toInputMonth($parteFin);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $fila
     */
    public static function combinarDesdeFormulario(array $fila, string $key = 'fechas_laboradas'): string
    {
        $inicio = self::resolverPeriodo($fila, $key.self::SUFIJO_INICIO);
        $fin = self::resolverPeriodo($fila, $key.self::SUFIJO_FIN);
        $actual = ! empty($fila[$key.self::SUFIJO_ACTUAL]);

        if ($inicio === '') {
            return trim((string) ($fila[$key] ?? ''));
        }

        try {
            $inicioFmt = self::formatearSalida($inicio);
        } catch (InvalidArgumentException) {
            return '';
        }

        if ($actual) {
            return $inicioFmt.' al Actual';
        }

        if ($fin === '') {
            return '';
        }

        try {
            $finFmt = self::formatearSalida($fin);
        } catch (InvalidArgumentException) {
            return '';
        }

        if (self::parsePeriodo($fin)->lt(self::parsePeriodo($inicio))) {
            return '';
        }

        return $inicioFmt.' al '.$finFmt;
    }

    /**
     * Causa concreta por la que un rango quedó inválido, para reemplazar el mensaje genérico.
     *
     * @param  array<string, mixed>  $fila
     */
    public static function motivoRangoInvalido(array $fila, string $key): ?string
    {
        if (! empty($fila[$key.self::SUFIJO_ACTUAL])) {
            return null;
        }

        $inicio = self::resolverPeriodo($fila, $key.self::SUFIJO_INICIO);
        $fin = self::resolverPeriodo($fila, $key.self::SUFIJO_FIN);

        if ($inicio === '') {
            return null;
        }

        if ($fin === '') {
            return 'Seleccione el mes y año en que terminó. Si todavía trabaja ahí, marque «Sigue laborando».';
        }

        if (self::parsePeriodo($fin)->lt(self::parsePeriodo($inicio))) {
            return 'La fecha de fin no puede ser anterior a la de inicio.';
        }

        return null;
    }

    /** Normaliza texto guardado para el informe Word (legacy « - » → « al »). */
    public static function formatearParaInforme(?string $valor): string
    {
        $valor = trim((string) $valor);
        if ($valor === '') {
            return '';
        }

        if (preg_match('/\s+(?:al|a)\s+/iu', $valor)) {
            return $valor;
        }

        if (preg_match('/\s*-\s*/', $valor)) {
            $partes = preg_split('/\s*-\s*/', $valor, 2);
            if (count($partes) === 2 && trim($partes[1]) !== '') {
                return trim($partes[0]).' al '.trim($partes[1]);
            }
        }

        return $valor;
    }

    public static function formatearSalida(string $valor): string
    {
        if (preg_match('/^(\d{4})-(\d{2})$/', $valor, $m)) {
            return sprintf('%02d/%04d', (int) $m[2], (int) $m[1]);
        }

        $fecha = Carbon::createFromFormat('Y-m-d', $valor);

        return $fecha->format('d/m/Y');
    }

    private static function parsePeriodo(string $valor): Carbon
    {
        if (preg_match('/^(\d{4})-(\d{2})$/', $valor)) {
            return Carbon::createFromFormat('Y-m', $valor)->startOfMonth();
        }

        return Carbon::createFromFormat('Y-m-d', $valor)->startOfDay();
    }

    private static function toInputMonth(string $parte): string
    {
        $parte = trim($parte);

        if ($parte === '' || strcasecmp($parte, 'Actual') === 0) {
            return '';
        }

        if (preg_match('/^(\d{4})-(\d{2})$/', $parte, $m)) {
            return sprintf('%04d-%02d', (int) $m[1], (int) $m[2]);
        }

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $parte, $m)) {
            return sprintf('%04d-%02d', (int) $m[1], (int) $m[2]);
        }

        if (preg_match('/^(\d{1,2})\/(\d{4})$/', $parte, $m)) {
            return sprintf('%04d-%02d', (int) $m[2], (int) $m[1]);
        }

        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $parte, $m)) {
            return sprintf('%04d-%02d', (int) $m[3], (int) $m[2]);
        }

        return '';
    }
}
