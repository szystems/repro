<?php

namespace App\Support;

/** E2.12 — Datos económicos (internos). Literales POLIGRAFO PRESENCIAL (2).pdf. */
class SituacionEconomicaCampos
{
    public const LABEL_ES_FIADOR = '¿Es fiador de alguien? (si la respuesta es afirmativa ampliar información)';

    public const LABEL_PROBLEMAS_BANCARIOS = '¿Tiene problemas o ha tenido problemas con sus cuentas bancarias? (si la respuesta es afirmativa ampliar información)';

    public const LABEL_VIVIENDA = '¿Dónde vive actualmente es propio o alquila? ¿Cuanto paga de alquiler?';

    public const LABEL_DEPENDIENTES = '¿Cuántas personas dependen económicamente de usted? ¿Quienes?';

    public const LABEL_INGRESOS_ADICIONALES = '¿Tiene algún ingreso adicional? (Amplié información y cuanto de ingreso mensual obtiene de ello)';

    public const LABEL_PROPIEDADES = '¿Tiene alguna propiedad a su nombre? (Amplié información)';

    public const LABEL_VEHICULOS = 'Tiene vehículo propio (describa cuantos, tipo de vehículo, modelo, marca)';

    public const LABEL_DEMANDAS = '¿Tiene o tuvo alguna demanda por alguna deuda? (Amplié información)';

    public const LABEL_PRETENSION = '¿Cuál es su pretensión salarial?';

    public const LABEL_GASTOS_MENSUALES = '¿A cuánto ascienden sus gastos mensuales?';

    public const LABEL_SAT = '¿Tiene algún omiso en SAT?';

    public const LABEL_PATRIMONIO_SOCIO =
        '¿Monto aproximado del valor su patrimonio? (vehiculos, propiedad, electrodomesticos, objetos de su propiedad, entre otros)';

    /** @return array<string, mixed> */
    public static function reglasValidacion(): array
    {
        return [
            'tiene_deudas' => 'required|in:si,no',
            'detalle_deudas' => 'nullable|string|max:2000',
            'econ_es_fiador' => 'required|in:si,no',
            'econ_detalle_es_fiador' => 'nullable|required_if:econ_es_fiador,si|string|max:2000',
            'econ_problemas_bancarios' => 'required|in:si,no',
            'econ_detalle_problemas_bancarios' => 'nullable|required_if:econ_problemas_bancarios,si|string|max:2000',
            'tipo_vivienda' => 'required|string|in:propia_pagada,propia_pagando,alquilada,prestada,familiar,otro',
            'monto_hipoteca' => 'nullable|numeric|min:0',
            'anos_restantes_hipoteca' => 'nullable|integer|min:0|max:50',
            'monto_alquiler' => 'nullable|numeric|min:0',
            'personas_contribuyen_gastos' => 'required|integer|min:1|max:20',
            'personas_hogar' => 'required|integer|min:1|max:50',
            'dependientes_economicos' => 'required|integer|min:0|max:20',
            'econ_tipo_vivienda_detalle' => 'nullable|string|max:500',
            'econ_dependientes_detalle' => 'required|string|max:1000',
            'econ_ingresos_adicionales_detalle' => 'required|string|max:1000',
            'econ_posee_propiedades' => 'required|in:si,no',
            'econ_detalle_propiedades' => 'nullable|required_if:econ_posee_propiedades,si|string|max:2000',
            'econ_posee_vehiculos' => 'required|in:si,no',
            'econ_detalle_vehiculos' => 'nullable|required_if:econ_posee_vehiculos,si|string|max:1000',
            'econ_demandas_deudas' => 'required|in:si,no',
            'econ_detalle_demandas' => 'nullable|required_if:econ_demandas_deudas,si|string|max:2000',
            'econ_pretension_salarial' => 'required|numeric|min:0',
            'econ_gastos_mensuales_aprox' => 'required|numeric|min:0',
            'econ_problemas_sat' => 'required|in:si,no',
            'econ_detalle_sat' => 'nullable|required_if:econ_problemas_sat,si|string|max:2000',
            'econ_patrimonio_aprox' => 'nullable|string|max:500',
            // Legacy — cuadrícula ingresos/gastos retirada del formulario
            'ingresos_principales' => 'nullable|numeric|min:0|max:999999.99',
            'ingresos_adicionales' => 'nullable|numeric|min:0|max:999999.99',
            'ingresos_familiares' => 'nullable|numeric|min:0|max:999999.99',
            'total_ingresos' => 'nullable|numeric|min:0',
            'gastos_vivienda' => 'nullable|numeric|min:0|max:99999.99',
            'gastos_alimentacion' => 'nullable|numeric|min:0|max:99999.99',
            'gastos_transporte' => 'nullable|numeric|min:0|max:99999.99',
            'gastos_educacion' => 'nullable|numeric|min:0|max:99999.99',
            'gastos_salud' => 'nullable|numeric|min:0|max:99999.99',
            'gastos_otros' => 'nullable|numeric|min:0|max:99999.99',
            'total_gastos' => 'nullable|numeric|min:0',
            'balance_mensual' => 'nullable|numeric',
            'tiene_ahorros' => 'nullable|in:si,no',
            'observaciones_economicas' => 'nullable|string|max:2000',
            'econ_monto_alquiler' => 'nullable|numeric|min:0',
            'econ_tiene_fiador' => 'nullable|in:si,no',
            'econ_detalle_fiador' => 'nullable|string|max:500',
        ];
    }

    /** @return array<string, string> */
    public static function mensajesValidacion(): array
    {
        return [
            'tiene_deudas.required' => 'Debe indicar si tiene deudas.',
            'econ_es_fiador.required' => 'Debe indicar si es fiador de alguien.',
            'econ_detalle_es_fiador.required_if' => 'Amplíe la información sobre la persona de la que es fiador.',
            'tipo_vivienda.required' => 'Debe seleccionar el tipo de vivienda.',
            'tipo_vivienda.in' => 'Seleccione un tipo de vivienda válido.',
            'personas_contribuyen_gastos.required' => 'Debe indicar cuántas personas contribuyen a los gastos.',
            'personas_contribuyen_gastos.min' => 'Al menos 1 persona debe contribuir a los gastos.',
            'personas_hogar.required' => 'Debe indicar cuántas personas viven en su hogar.',
            'personas_hogar.integer' => 'El número de personas debe ser un número entero.',
            'personas_hogar.min' => 'Debe haber al menos 1 persona en el hogar.',
            'dependientes_economicos.required' => 'Debe indicar el número de dependientes económicos.',
            'econ_dependientes_detalle.required' => 'Indique cuántas personas dependen económicamente de usted.',
            'econ_ingresos_adicionales_detalle.required' => 'Indique si tiene ingresos adicionales y de qué monto.',
            'econ_detalle_propiedades.required_if' => 'Describa las propiedades que posee.',
            'econ_detalle_vehiculos.required_if' => 'Describa los vehículos que posee.',
            'econ_detalle_problemas_bancarios.required_if' => 'Describa los problemas bancarios.',
            'econ_detalle_demandas.required_if' => 'Describa las demandas por deudas.',
            'econ_pretension_salarial.required' => 'Indique su pretensión salarial.',
            'econ_gastos_mensuales_aprox.required' => 'Indique el monto aproximado de sus gastos mensuales.',
            'econ_detalle_sat.required_if' => 'Describa los problemas con SAT.',
        ];
    }
}
