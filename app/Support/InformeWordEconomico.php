<?php

namespace App\Support;

use App\Models\Cuestionario;

/**
 * Narrativa del apartado «ASPECTO ECONÓMICO» del informe Word.
 *
 * Cuando el evaluador no ha redactado el bloque «Información económica», la sección se entregaba
 * en blanco aunque el candidato sí hubiera respondido la sección económica del cuestionario. Aquí
 * se compilan esas respuestas para que el apartado siempre lleve contenido; el evaluador puede
 * sustituirlo escribiendo su propio texto o editarlo en Word.
 */
class InformeWordEconomico
{
    private const SECCION_ECONOMICA = 4;

    private const TIPOS_VIVIENDA = [
        'propia_pagada' => 'propia, totalmente pagada',
        'propia_pagando' => 'propia, pagando hipoteca',
        'alquilada' => 'alquilada',
        'prestada' => 'prestada',
        'familiar' => 'casa familiar',
        'otro' => 'otro',
    ];

    public static function narrativa(?Cuestionario $cuestionario): string
    {
        if ($cuestionario === null) {
            return '';
        }

        $respuestas = $cuestionario->obtenerRespuestasSeccion(self::SECCION_ECONOMICA);
        if ($respuestas === []) {
            return '';
        }

        $lineas = array_merge(
            self::lineasVivienda($respuestas),
            self::lineasHogar($respuestas),
            self::lineasIngresosYGastos($respuestas),
            self::lineasPatrimonio($respuestas),
            self::lineasCompromisos($respuestas)
        );

        return implode("\n", array_values(array_filter($lineas)));
    }

    /**
     * @param  array<string, mixed>  $respuestas
     * @return list<string>
     */
    private static function lineasVivienda(array $respuestas): array
    {
        $tipo = self::texto($respuestas['tipo_vivienda'] ?? '');
        if ($tipo === '') {
            return [];
        }

        $descripcion = 'Vivienda: ' . (self::TIPOS_VIVIENDA[$tipo] ?? $tipo);

        $alquiler = self::monto($respuestas['monto_alquiler'] ?? $respuestas['econ_monto_alquiler'] ?? '');
        if ($alquiler !== '') {
            $descripcion .= ', paga ' . $alquiler . ' de alquiler mensual';
        }

        $hipoteca = self::monto($respuestas['monto_hipoteca'] ?? '');
        if ($hipoteca !== '') {
            $descripcion .= ', cuota de hipoteca ' . $hipoteca . ' mensuales';

            $anios = self::texto($respuestas['anos_restantes_hipoteca'] ?? '');
            if ($anios !== '' && $anios !== '0') {
                $descripcion .= ' (' . $anios . ' año(s) restantes)';
            }
        }

        $detalle = self::texto($respuestas['econ_tipo_vivienda_detalle'] ?? '');
        if ($detalle !== '') {
            $descripcion .= '. ' . $detalle;
        }

        return [$descripcion . '.'];
    }

    /**
     * @param  array<string, mixed>  $respuestas
     * @return list<string>
     */
    private static function lineasHogar(array $respuestas): array
    {
        $lineas = [];

        $hogar = self::texto($respuestas['personas_hogar'] ?? '');
        $contribuyen = self::texto($respuestas['personas_contribuyen_gastos'] ?? '');
        if ($hogar !== '' || $contribuyen !== '') {
            $partes = [];
            if ($hogar !== '') {
                $partes[] = $hogar . ' persona(s) integran el hogar';
            }
            if ($contribuyen !== '') {
                $partes[] = $contribuyen . ' aporta(n) a los gastos';
            }
            $lineas[] = 'Hogar: ' . implode('; ', $partes) . '.';
        }

        $dependientes = self::texto($respuestas['dependientes_economicos'] ?? '');
        $detalleDependientes = self::texto($respuestas['econ_dependientes_detalle'] ?? '');
        if ($dependientes !== '' || $detalleDependientes !== '') {
            $texto = 'Dependientes económicos: ';
            $texto .= $dependientes !== '' ? $dependientes : 'según lo indicado';
            if ($detalleDependientes !== '') {
                $texto .= ' (' . $detalleDependientes . ')';
            }
            $lineas[] = $texto . '.';
        }

        return $lineas;
    }

    /**
     * @param  array<string, mixed>  $respuestas
     * @return list<string>
     */
    private static function lineasIngresosYGastos(array $respuestas): array
    {
        $lineas = [];

        $adicionales = self::texto($respuestas['econ_ingresos_adicionales_detalle'] ?? '');
        if ($adicionales !== '') {
            $lineas[] = 'Ingresos adicionales: ' . $adicionales . '.';
        }

        $gastos = self::monto($respuestas['econ_gastos_mensuales_aprox'] ?? '');
        if ($gastos !== '') {
            $lineas[] = 'Gastos mensuales aproximados: ' . $gastos . '.';
        }

        $pretension = self::monto($respuestas['econ_pretension_salarial'] ?? '');
        if ($pretension !== '') {
            $lineas[] = 'Pretensión salarial: ' . $pretension . '.';
        }

        return $lineas;
    }

    /**
     * @param  array<string, mixed>  $respuestas
     * @return list<string>
     */
    private static function lineasPatrimonio(array $respuestas): array
    {
        $lineas = [];

        $lineas[] = self::lineaSiNo(
            $respuestas,
            'econ_posee_propiedades',
            'econ_detalle_propiedades',
            'Indicó tener propiedades a su nombre',
            'Indicó no tener propiedades a su nombre'
        );

        $lineas[] = self::lineaSiNo(
            $respuestas,
            'econ_posee_vehiculos',
            'econ_detalle_vehiculos',
            'Indicó tener vehículo propio',
            'Indicó no tener vehículo propio'
        );

        $patrimonio = self::texto($respuestas['econ_patrimonio_aprox'] ?? '');
        if ($patrimonio !== '') {
            $lineas[] = 'Valor aproximado de su patrimonio: ' . $patrimonio . '.';
        }

        return array_values(array_filter($lineas));
    }

    /**
     * @param  array<string, mixed>  $respuestas
     * @return list<string>
     */
    private static function lineasCompromisos(array $respuestas): array
    {
        $lineas = [];

        $tieneDeudas = self::texto($respuestas['tiene_deudas'] ?? '');
        $detalleDeudas = self::texto($respuestas['detalle_deudas'] ?? '');
        if ($tieneDeudas === 'no') {
            $lineas[] = 'Indicó no tener deudas vigentes.';
        } elseif ($detalleDeudas !== '') {
            $lineas[] = 'Sobre sus deudas indicó: ' . $detalleDeudas . '.';
        }

        $lineas[] = self::lineaSiNo(
            $respuestas,
            'econ_es_fiador',
            'econ_detalle_es_fiador',
            'Indicó ser fiador de otra persona',
            'Indicó no ser fiador de ninguna persona'
        );

        $lineas[] = self::lineaSiNo(
            $respuestas,
            'econ_problemas_bancarios',
            'econ_detalle_problemas_bancarios',
            'Indicó haber tenido problemas con sus cuentas bancarias',
            'Indicó no haber tenido problemas con sus cuentas bancarias'
        );

        $lineas[] = self::lineaSiNo(
            $respuestas,
            'econ_demandas_deudas',
            'econ_detalle_demandas',
            'Indicó tener o haber tenido demandas por deudas',
            'Indicó no tener ni haber tenido demandas por deudas'
        );

        $lineas[] = self::lineaSiNo(
            $respuestas,
            'econ_problemas_sat',
            'econ_detalle_sat',
            'Indicó tener omisos ante la SAT',
            'Indicó no tener omisos ante la SAT'
        );

        return array_values(array_filter($lineas));
    }

    /** @param array<string, mixed> $respuestas */
    private static function lineaSiNo(
        array $respuestas,
        string $campo,
        string $campoDetalle,
        string $textoSi,
        string $textoNo
    ): string {
        $valor = self::texto($respuestas[$campo] ?? '');
        if ($valor === '') {
            return '';
        }

        if ($valor !== 'si') {
            return $textoNo . '.';
        }

        $detalle = self::texto($respuestas[$campoDetalle] ?? '');

        return $detalle !== ''
            ? $textoSi . ': ' . $detalle . '.'
            : $textoSi . '.';
    }

    private static function monto(mixed $valor): string
    {
        $texto = self::texto($valor);
        if ($texto === '' || ! is_numeric($texto)) {
            return $texto;
        }

        return 'Q. ' . number_format((float) $texto, 2);
    }

    private static function texto(mixed $valor): string
    {
        if (is_array($valor)) {
            return '';
        }

        return trim((string) $valor);
    }
}
