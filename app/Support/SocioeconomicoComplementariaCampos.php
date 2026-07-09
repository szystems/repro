<?php

namespace App\Support;

/** Campos de la sección 6 exclusiva del formulario Socioeconómico (E4.2–4.5). */
class SocioeconomicoComplementariaCampos
{
    /** @return array<string, mixed> */
    public static function reglasValidacion(): array
    {
        return array_merge(
            [
                'referencias_familiares' => 'required|array|min:2',
                'referencias_personales' => 'required|array|min:2',
                'referencias_vecinales' => 'required|array|min:1',
                'referencias_laborales' => 'nullable|array',
                'bienes' => 'nullable|array',
                'presupuesto' => 'nullable|array',
                'bienes_total' => 'nullable|numeric|min:0',
                'presupuesto_total' => 'nullable|numeric|min:0',
                'viv_tiempo_residencia' => 'required|string|max:100',
                'viv_tipo_vivienda' => 'required|in:propia,alquilada,familiar,prestada,otro',
                'viv_num_habitantes' => 'required|integer|min:1|max:50',
                'viv_refs_ubicacion' => 'nullable|string|max:2000',
                'viv_zona_riesgo' => 'required|in:si,no',
                'viv_direcciones_anteriores' => 'nullable|string|max:3000',
            ],
            TablaDinamica::reglasValidacion(6, 'socioeconomico'),
            self::reglasCondicionalesVivienda()
        );
    }

    /** @return array<string, mixed> */
    private static function reglasCondicionalesVivienda(): array
    {
        return [
            'viv_propietario' => 'required_if:viv_tipo_vivienda,alquilada,familiar,prestada|nullable|string|max:150',
            'viv_monto_alquiler' => 'required_if:viv_tipo_vivienda,alquilada|nullable|numeric|min:0',
            'viv_detalle_zona_riesgo' => 'required_if:viv_zona_riesgo,si|nullable|string|max:2000',
        ];
    }

    /** @return array<string, string> */
    public static function mensajesValidacion(): array
    {
        return array_merge(TablaDinamica::mensajesValidacion(), [
            'referencias_familiares.required' => 'Debe registrar al menos 2 referencias familiares.',
            'referencias_familiares.min' => 'Debe registrar al menos 2 referencias familiares.',
            'referencias_personales.required' => 'Debe registrar al menos 2 referencias personales.',
            'referencias_personales.min' => 'Debe registrar al menos 2 referencias personales.',
            'referencias_vecinales.required' => 'Debe registrar al menos 1 referencia vecinal.',
            'referencias_vecinales.min' => 'Debe registrar al menos 1 referencia vecinal.',
            'viv_tiempo_residencia.required' => 'Indique el tiempo de residencia en su domicilio actual.',
            'viv_tipo_vivienda.required' => 'Seleccione el tipo de vivienda.',
            'viv_num_habitantes.required' => 'Indique cuántas personas habitan la vivienda.',
            'viv_zona_riesgo.required' => 'Indique si la zona donde vive es de riesgo.',
            'viv_propietario.required_if' => 'Indique el nombre del propietario o familiar.',
            'viv_monto_alquiler.required_if' => 'Indique el monto de alquiler mensual.',
            'viv_detalle_zona_riesgo.required_if' => 'Describa la zona de riesgo.',
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
     * Calcula totales de bienes y presupuesto a partir de filas de tabla.
     *
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
