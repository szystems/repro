<?php

namespace App\Http\Requests\Cuestionario;

use Illuminate\Foundation\Http\FormRequest;

class SituacionEconomicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Ingresos
            'ingreso_mensual_personal' => 'required|numeric|min:0|max:999999.99',
            'ingreso_mensual_conyugue' => 'nullable|numeric|min:0|max:999999.99',
            'otros_ingresos' => 'nullable|numeric|min:0|max:999999.99',
            'detalle_otros_ingresos' => 'required_if:otros_ingresos,>0|nullable|string|max:500',
            'ingreso_familiar_total' => 'required|numeric|min:0|max:999999.99',
            
            // Gastos mensuales
            'gasto_vivienda' => 'required|numeric|min:0|max:99999.99',
            'gasto_alimentacion' => 'required|numeric|min:0|max:99999.99',
            'gasto_transporte' => 'required|numeric|min:0|max:99999.99',
            'gasto_servicios' => 'required|numeric|min:0|max:99999.99',
            'gasto_educacion' => 'nullable|numeric|min:0|max:99999.99',
            'gasto_salud' => 'nullable|numeric|min:0|max:99999.99',
            'gasto_entretenimiento' => 'nullable|numeric|min:0|max:99999.99',
            'otros_gastos' => 'nullable|numeric|min:0|max:99999.99',
            'detalle_otros_gastos' => 'required_if:otros_gastos,>0|nullable|string|max:500',
            'gastos_totales' => 'required|numeric|min:0|max:999999.99',
            
            // Bienes inmuebles
            'tiene_casa_propia' => 'required|boolean',
            'casa_valor_estimado' => 'required_if:tiene_casa_propia,true|nullable|numeric|min:0',
            'casa_hipotecada' => 'required_if:tiene_casa_propia,true|nullable|boolean',
            'casa_saldo_hipoteca' => 'required_if:casa_hipotecada,true|nullable|numeric|min:0',
            'casa_pago_mensual' => 'required_if:casa_hipotecada,true|nullable|numeric|min:0',
            
            'otros_inmuebles' => 'nullable|array',
            'otros_inmuebles.*.tipo' => 'required|string|max:100',
            'otros_inmuebles.*.valor' => 'required|numeric|min:0',
            'otros_inmuebles.*.hipotecado' => 'required|boolean',
            'otros_inmuebles.*.saldo_deuda' => 'nullable|numeric|min:0',
            
            // Vehículos
            'tiene_vehiculo' => 'required|boolean',
            'vehiculos' => 'nullable|array',
            'vehiculos.*.marca' => 'required|string|max:100',
            'vehiculos.*.modelo' => 'required|string|max:100',
            'vehiculos.*.año' => 'required|integer|between:1980,2030',
            'vehiculos.*.valor_estimado' => 'required|numeric|min:0',
            'vehiculos.*.financiado' => 'required|boolean',
            'vehiculos.*.saldo_deuda' => 'nullable|numeric|min:0',
            'vehiculos.*.pago_mensual' => 'nullable|numeric|min:0',
            
            // Cuentas bancarias
            'cuentas_bancarias' => 'nullable|array',
            'cuentas_bancarias.*.banco' => 'required|string|max:100',
            'cuentas_bancarias.*.tipo_cuenta' => 'required|in:ahorro,monetaria,plazo_fijo',
            'cuentas_bancarias.*.saldo_aproximado' => 'required|numeric|min:0',
            
            // Deudas y obligaciones
            'tarjetas_credito' => 'nullable|array',
            'tarjetas_credito.*.banco' => 'required|string|max:100',
            'tarjetas_credito.*.limite' => 'required|numeric|min:0',
            'tarjetas_credito.*.saldo_actual' => 'required|numeric|min:0',
            'tarjetas_credito.*.pago_minimo' => 'required|numeric|min:0',
            
            'prestamos_personales' => 'nullable|array',
            'prestamos_personales.*.institucion' => 'required|string|max:100',
            'prestamos_personales.*.saldo_actual' => 'required|numeric|min:0',
            'prestamos_personales.*.pago_mensual' => 'required|numeric|min:0',
            'prestamos_personales.*.plazo_restante' => 'required|integer|min:1',
            
            // Seguros
            'seguros' => 'nullable|array',
            'seguros.*.tipo' => 'required|in:vida,vehicular,hogar,medico,otros',
            'seguros.*.aseguradora' => 'required|string|max:100',
            'seguros.*.prima_mensual' => 'required|numeric|min:0',
            
            // Referencias comerciales
            'referencia_comercial_1_empresa' => 'required|string|max:255',
            'referencia_comercial_1_telefono' => 'required|string|max:20',
            'referencia_comercial_1_tipo' => 'required|in:banco,tienda,prestamista,otro',
            
            'referencia_comercial_2_empresa' => 'nullable|string|max:255',
            'referencia_comercial_2_telefono' => 'nullable|string|max:20',
            'referencia_comercial_2_tipo' => 'nullable|in:banco,tienda,prestamista,otro',
        ];
    }

    public function messages(): array
    {
        return [
            'ingreso_mensual_personal.required' => 'Su ingreso mensual personal es obligatorio.',
            'ingreso_familiar_total.required' => 'El ingreso familiar total es obligatorio.',
            'gasto_vivienda.required' => 'El gasto en vivienda es obligatorio.',
            'gasto_alimentacion.required' => 'El gasto en alimentación es obligatorio.',
            'gasto_transporte.required' => 'El gasto en transporte es obligatorio.',
            'gasto_servicios.required' => 'El gasto en servicios es obligatorio.',
            'gastos_totales.required' => 'Los gastos totales son obligatorios.',
            'tiene_casa_propia.required' => 'Debe indicar si tiene casa propia.',
            'tiene_vehiculo.required' => 'Debe indicar si tiene vehículo.',
            'referencia_comercial_1_empresa.required' => 'Debe proporcionar al menos una referencia comercial.',
        ];
    }
}
