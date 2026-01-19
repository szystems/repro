<?php

namespace App\Http\Requests\Cuestionario;

use Illuminate\Foundation\Http\FormRequest;

class AntecedentesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Antecedentes penales
            'antecedentes_penales' => 'required|boolean',
            'detalle_antecedentes_penales' => 'required_if:antecedentes_penales,true|nullable|string|max:1000',
            'fecha_antecedente_penal' => 'required_if:antecedentes_penales,true|nullable|date|before_or_equal:today',
            
            // Problemas legales actuales
            'problemas_legales_actuales' => 'required|boolean',
            'detalle_problemas_legales' => 'required_if:problemas_legales_actuales,true|nullable|string|max:1000',
            
            // Demandas civiles
            'demandas_civiles' => 'required|boolean',
            'detalle_demandas_civiles' => 'required_if:demandas_civiles,true|nullable|string|max:1000',
            
            // Adicciones y hábitos
            'consume_alcohol' => 'required|in:nunca,ocasionalmente,frecuentemente,diariamente',
            'detalle_consumo_alcohol' => 'required_unless:consume_alcohol,nunca|nullable|string|max:500',
            
            'consume_drogas' => 'required|boolean',
            'detalle_consumo_drogas' => 'required_if:consume_drogas,true|nullable|string|max:1000',
            'tratamiento_adicciones' => 'required_if:consume_drogas,true|nullable|boolean',
            
            'fuma_cigarrillos' => 'required|boolean',
            'cantidad_cigarrillos_dia' => 'required_if:fuma_cigarrillos,true|nullable|integer|between:1,100',
            
            // Salud mental
            'tratamiento_psicologico' => 'required|boolean',
            'detalle_tratamiento_psicologico' => 'required_if:tratamiento_psicologico,true|nullable|string|max:500',
            'medicamentos_psiquiatricos' => 'required|boolean',
            'detalle_medicamentos' => 'required_if:medicamentos_psiquiatricos,true|nullable|string|max:500',
            
            // Enfermedades importantes
            'enfermedades_importantes' => 'required|boolean',
            'detalle_enfermedades' => 'required_if:enfermedades_importantes,true|nullable|string|max:500',
            'medicamentos_actuales' => 'nullable|string|max:500',
            
            // Accidentes o incidentes
            'accidentes_importantes' => 'required|boolean',
            'detalle_accidentes' => 'required_if:accidentes_importantes,true|nullable|string|max:500',
            
            // Actividades riesgosas
            'practica_deportes_extremos' => 'required|boolean',
            'detalle_deportes_extremos' => 'required_if:practica_deportes_extremos,true|nullable|string|max:500',
            
            // Situaciones comprometedoras
            'situaciones_comprometedoras' => 'required|boolean',
            'detalle_situaciones_comprometedoras' => 'required_if:situaciones_comprometedoras,true|nullable|string|max:1000',
            
            // Redes sociales y tecnología
            'redes_sociales_principales' => 'nullable|array',
            'redes_sociales_principales.*' => 'string|in:facebook,instagram,twitter,linkedin,tiktok,youtube,otros',
            'tiempo_diario_redes_sociales' => 'required|integer|between:0,20',
            'juegos_en_linea' => 'required|boolean',
            'tiempo_juegos_linea' => 'required_if:juegos_en_linea,true|nullable|integer|between:0,20',
            
            // Referencias personales (no familiares ni laborales)
            'referencia_personal_1_nombre' => 'required|string|max:255',
            'referencia_personal_1_telefono' => 'required|string|max:20',
            'referencia_personal_1_profesion' => 'required|string|max:255',
            'referencia_personal_1_tiempo_conoce' => 'required|integer|between:1,50',
            'referencia_personal_1_como_conoce' => 'required|string|max:500',
            
            'referencia_personal_2_nombre' => 'required|string|max:255',
            'referencia_personal_2_telefono' => 'required|string|max:20',
            'referencia_personal_2_profesion' => 'required|string|max:255',
            'referencia_personal_2_tiempo_conoce' => 'required|integer|between:1,50',
            'referencia_personal_2_como_conoce' => 'required|string|max:500',
            
            'referencia_personal_3_nombre' => 'nullable|string|max:255',
            'referencia_personal_3_telefono' => 'nullable|string|max:20',
            'referencia_personal_3_profesion' => 'nullable|string|max:255',
            'referencia_personal_3_tiempo_conoce' => 'nullable|integer|between:1,50',
            'referencia_personal_3_como_conoce' => 'nullable|string|max:500',
            
            // Declaraciones importantes
            'informacion_adicional' => 'nullable|string|max:2000',
            'acepta_investigacion' => 'required|boolean',
            'acepta_veracidad' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'antecedentes_penales.required' => 'Debe indicar si tiene antecedentes penales.',
            'detalle_antecedentes_penales.required_if' => 'Si tiene antecedentes penales, debe proporcionar detalles.',
            'problemas_legales_actuales.required' => 'Debe indicar si tiene problemas legales actuales.',
            'demandas_civiles.required' => 'Debe indicar si tiene demandas civiles.',
            'consume_alcohol.required' => 'Debe indicar su frecuencia de consumo de alcohol.',
            'consume_drogas.required' => 'Debe indicar si consume drogas.',
            'fuma_cigarrillos.required' => 'Debe indicar si fuma cigarrillos.',
            'tratamiento_psicologico.required' => 'Debe indicar si ha recibido tratamiento psicológico.',
            'medicamentos_psiquiatricos.required' => 'Debe indicar si toma medicamentos psiquiátricos.',
            'enfermedades_importantes.required' => 'Debe indicar si tiene enfermedades importantes.',
            'referencia_personal_1_nombre.required' => 'Debe proporcionar al menos dos referencias personales.',
            'referencia_personal_2_nombre.required' => 'Debe proporcionar al menos dos referencias personales.',
            'acepta_investigacion.required' => 'Debe aceptar que se investigue la información proporcionada.',
            'acepta_veracidad.required' => 'Debe declarar que la información es verídica.',
            'time_diario_redes_sociales.required' => 'Debe indicar cuánto tiempo dedica a redes sociales.',
        ];
    }

    public function attributes(): array
    {
        return [
            'antecedentes_penales' => 'antecedentes penales',
            'problemas_legales_actuales' => 'problemas legales actuales',
            'demandas_civiles' => 'demandas civiles',
            'consume_alcohol' => 'consumo de alcohol',
            'consume_drogas' => 'consumo de drogas',
            'fuma_cigarrillos' => 'fumar cigarrillos',
            'tratamiento_psicologico' => 'tratamiento psicológico',
            'medicamentos_psiquiatricos' => 'medicamentos psiquiátricos',
            'enfermedades_importantes' => 'enfermedades importantes',
            'acepta_investigacion' => 'aceptación de investigación',
            'acepta_veracidad' => 'declaración de veracidad',
        ];
    }
}
