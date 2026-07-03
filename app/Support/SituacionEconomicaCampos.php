<?php

namespace App\Support;

/** E2.12 — Situación económica general ampliada (interna). */
class SituacionEconomicaCampos
{
    /** @return array<string, mixed> */
    public static function reglasValidacion(): array
    {
        return [
            'econ_tipo_vivienda_detalle' => 'nullable|string|max:100',
            'econ_monto_alquiler' => 'nullable|numeric|min:0',
            'econ_ingresos_adicionales_detalle' => 'nullable|string|max:1000',
            'econ_posee_propiedades' => 'required|in:si,no',
            'econ_detalle_propiedades' => 'nullable|required_if:econ_posee_propiedades,si|string|max:2000',
            'econ_posee_vehiculos' => 'required|in:si,no',
            'econ_detalle_vehiculos' => 'nullable|required_if:econ_posee_vehiculos,si|string|max:1000',
            'econ_pretension_salarial' => 'nullable|numeric|min:0',
            'econ_gastos_mensuales_aprox' => 'nullable|numeric|min:0',
            'econ_tiene_fiador' => 'required|in:si,no',
            'econ_detalle_fiador' => 'nullable|string|max:500',
            'econ_problemas_bancarios' => 'required|in:si,no',
            'econ_detalle_problemas_bancarios' => 'nullable|required_if:econ_problemas_bancarios,si|string|max:2000',
            'econ_demandas_deudas' => 'required|in:si,no',
            'econ_detalle_demandas' => 'nullable|required_if:econ_demandas_deudas,si|string|max:2000',
            'econ_problemas_sat' => 'required|in:si,no',
            'econ_detalle_sat' => 'nullable|required_if:econ_problemas_sat,si|string|max:2000',
        ];
    }

    /** @return array<string, string> */
    public static function mensajesValidacion(): array
    {
        return [
            'econ_detalle_propiedades.required_if' => 'Describa las propiedades que posee.',
            'econ_detalle_vehiculos.required_if' => 'Describa los vehículos que posee (marca, modelo, año, etc.).',
            'econ_detalle_problemas_bancarios.required_if' => 'Describa los problemas bancarios.',
            'econ_detalle_demandas.required_if' => 'Describa las demandas por deudas.',
            'econ_detalle_sat.required_if' => 'Describa los problemas con SAT.',
        ];
    }
}
