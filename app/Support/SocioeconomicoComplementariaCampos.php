<?php

namespace App\Support;

/** Campos de la sección 6 exclusiva del formulario Socioeconómico (E4.2–4.5). Literales SOCIOECONOMICO PDF. */
class SocioeconomicoComplementariaCampos
{
    public const TITULO_REFERENCIAS_FAMILIARES =
        'REFERENCIAS FAMILIARES: (que no sean hermanos, padres o conyugue)';

    public const TITULO_GASTOS =
        'Gastos: (colocar la cantidad que gasta mensual en cada aspecto)';

    public const TITULO_VIVIENDA = 'Datos de vivienda:';

    public const LABEL_TIEMPO_RESIDENCIA = '¿Tiempo de residencia en su actual dirección?';

    public const LABEL_TIPO_VIVIENDA = '¿Es propio o alquila?';

    public const LABEL_MONTO_RENTA = '¿Cuanto paga de renta?';

    public const LABEL_PROPIETARIO = '¿Nombre del propietario de la vivienda?';

    public const LABEL_HABITANTES =
        'Cuantas personas viven en la residencia? Qué parentesco tienen con usted?';

    public const LABEL_REFS_UBICACION = 'Referencias de la ubicación de su vivienda:';

    public const LABEL_ZONA_ROJA = '¿Es considerado zona roja?';

    public const LABEL_DIRECCIONES_ANTERIORES = 'Direccion de viviendas anteriores:';

    public const LABEL_HA_LABORADO_EMPRESA =
        '¿Ha laborado anteriormente para la empresa donde está aplicando?';

    public const LABEL_PATRIMONIO =
        '¿Monto aproximado del valor su patrimonio? (vehiculos, propiedad, electrodomesticos, objetos de su propiedad, entre otros)';

    /** @var list<string> */
    public const CONCEPTOS_PRESUPUESTO_PDF = [
        'Alimentación:',
        'Cuota de alquiler:',
        'Vestuario:',
        'Transporte: (gasolina o pasajes)',
        'Pago de servicios básico (agua, luz, cable, teléfono, internet)',
        'Gastos méditos:',
        'Colegiaturas:',
        'Cuota mensual de préstamos:',
        'Cuota de manutención:',
        'Otros gastos:',
    ];

    /** @return list<array{concepto: string, monto: string}> */
    public static function filasPresupuestoIniciales(array $existentes = []): array
    {
        $porConcepto = [];
        foreach ($existentes as $fila) {
            if (! is_array($fila)) {
                continue;
            }
            $concepto = trim((string) ($fila['concepto'] ?? ''));
            if ($concepto !== '') {
                $porConcepto[$concepto] = (string) ($fila['monto'] ?? '');
            }
        }

        $filas = [];
        foreach (self::CONCEPTOS_PRESUPUESTO_PDF as $concepto) {
            $filas[] = [
                'concepto' => $concepto,
                'monto' => $porConcepto[$concepto] ?? $porConcepto[rtrim($concepto, ':')] ?? '',
            ];
        }

        return $filas;
    }

    /** @return array<string, mixed> */
    public static function reglasValidacion(): array
    {
        return array_merge(
            [
                'referencias_familiares' => 'required|array|min:3',
                'referencias_personales' => 'required|array|min:3',
                'referencias_vecinales' => 'required|array|min:1',
                'referencias_laborales' => 'nullable|array',
                'bienes' => 'nullable|array',
                'presupuesto' => 'required|array|min:10',
                'bienes_total' => 'nullable|numeric|min:0',
                'presupuesto_total' => 'nullable|numeric|min:0',
                'viv_tiempo_residencia' => 'required|string|max:100',
                'viv_tipo_vivienda_detalle' => 'required|string|max:500',
                'viv_monto_alquiler' => 'nullable|numeric|min:0',
                'viv_propietario' => 'nullable|string|max:150',
                'viv_habitantes_detalle' => 'required|string|max:2000',
                'viv_refs_ubicacion' => 'required|string|max:2000',
                'viv_zona_riesgo' => 'required|in:si,no',
                'viv_direcciones_anteriores' => 'nullable|string|max:3000',
                'comp_ha_laborado_empresa' => 'required|string|max:2000',
                // Legacy
                'viv_tipo_vivienda' => 'nullable|in:propia,alquilada,familiar,prestada,otro',
                'viv_num_habitantes' => 'nullable|integer|min:1|max:50',
                'viv_detalle_zona_riesgo' => 'nullable|string|max:2000',
            ],
            TablaDinamica::reglasValidacion(6, 'socioeconomico'),
            self::reglasCondicionalesVivienda()
        );
    }

    /** @return array<string, mixed> */
    private static function reglasCondicionalesVivienda(): array
    {
        return [
            'viv_detalle_zona_riesgo' => 'nullable|required_if:viv_zona_riesgo,si|string|max:2000',
        ];
    }

    /** @return array<string, string> */
    public static function mensajesValidacion(): array
    {
        return array_merge(TablaDinamica::mensajesValidacion(), [
            'referencias_familiares.required' => 'Debe registrar al menos 3 referencias familiares.',
            'referencias_familiares.min' => 'Debe registrar al menos 3 referencias familiares.',
            'referencias_personales.required' => 'Debe registrar al menos 3 referencias personales.',
            'referencias_personales.min' => 'Debe registrar al menos 3 referencias personales.',
            'referencias_vecinales.required' => 'Debe registrar al menos 1 referencia vecinal.',
            'referencias_vecinales.min' => 'Debe registrar al menos 1 referencia vecinal.',
            'presupuesto.required' => 'Complete el detalle de gastos mensuales.',
            'presupuesto.min' => 'Complete todos los rubros de gastos mensuales.',
            'viv_tiempo_residencia.required' => 'Indique el tiempo de residencia en su domicilio actual.',
            'viv_tipo_vivienda_detalle.required' => 'Indique si su vivienda es propia o alquilada.',
            'viv_habitantes_detalle.required' => 'Indique cuántas personas viven en la residencia y su parentesco.',
            'viv_refs_ubicacion.required' => 'Indique referencias de la ubicación de su vivienda.',
            'viv_zona_riesgo.required' => 'Indique si la zona donde vive es considerada zona roja.',
            'viv_detalle_zona_riesgo.required_if' => 'Describa la zona de riesgo.',
            'comp_ha_laborado_empresa.required' => 'Indique si ha laborado anteriormente para la empresa contratante.',
        ]);
    }

    /** @return array<string, string> */
    public static function tiposVivienda(): array
    {
        return [
            'propia' => 'Propia',
            'alquilada' => 'Alquilada',
            'familiar' => 'Familiar / prestada por familiar',
            'prestada' => 'Prestada (otro)',
            'otro' => 'Otro',
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $bienes
     * @param  array<int, array<string, mixed>>  $presupuesto
     * @return array{bienes_total: float, presupuesto_total: float}
     */
    public static function calcularTotales(array $bienes, array $presupuesto): array
    {
        $totalBienes = 0.0;
        foreach ($bienes as $fila) {
            $totalBienes += (float) ($fila['valor'] ?? 0);
        }

        $totalPresupuesto = 0.0;
        foreach ($presupuesto as $fila) {
            $totalPresupuesto += (float) ($fila['monto'] ?? 0);
        }

        return [
            'bienes_total' => round($totalBienes, 2),
            'presupuesto_total' => round($totalPresupuesto, 2),
        ];
    }
}
