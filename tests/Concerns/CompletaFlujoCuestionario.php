<?php

namespace Tests\Concerns;

trait CompletaFlujoCuestionario
{
    protected function aceptarInstrucciones(string $token): void
    {
        $this->post(route('cuestionario.aceptar-instrucciones', $token), [
            'acepta_instrucciones' => '1',
        ]);
    }

    protected function aceptarTerminosCuestionario(string $token, ?string $firma = null): void
    {
        $this->post(route('cuestionario.aceptar-terminos', $token), [
            'acepta_terminos' => '1',
            'firma_digital' => $firma ?? 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

        $evaluado = \App\Models\EvaluadoOrden::where('token_unico', $token)->first();
        if ($evaluado && \App\Support\AutorizacionesLegales::requiereInfornet($evaluado)) {
            $this->post(route('cuestionario.aceptar-infornet', $token), [
                'acepta_infornet' => '1',
            ]);
        }
    }

    protected function verificarIdentidadYFlujoPreSeccion(string $token, string $dpi): void
    {
        $this->post("/cuestionario/{$token}/verificar", ['dpi_ingresado' => $dpi]);
        $this->aceptarInstrucciones($token);
        $this->aceptarTerminosCuestionario($token);
    }

    /** @return array<string, mixed> */
    protected function atributosCuestionarioListoParaSecciones(): array
    {
        return [
            'instrucciones_leidas_at' => now(),
            'acepta_terminos' => true,
            'acepta_terminos_at' => now(),
            'acepta_infornet' => true,
            'acepta_infornet_at' => now(),
        ];
    }

    /** @return array<string, mixed> */
    protected function datosSeccion1Preempleo(array $extra = []): array
    {
        return array_merge([
            'nombres_completos' => 'Juan Carlos',
            'apellidos_completos' => 'Pérez García',
            'tipo_identificacion' => 'dpi',
            'dpi' => '1234567890101',
            'fecha_nacimiento' => '1990-05-15',
            'estado_civil' => 'soltero',
            'nacionalidad' => 'Guatemala',
            'departamento_nacimiento' => 'Guatemala',
            'municipio_nacimiento' => 'Guatemala',
            'direccion_residencia' => 'Ciudad de Guatemala',
            'departamento' => 'Guatemala',
            'municipio' => 'Guatemala',
            'telefono_personal' => '12345678',
            'email_personal' => 'juan@example.com',
            'licencia_conducir' => 'si',
            'foto_candidato' => \Tests\Support\FakeImage::jpeg('candidato.jpg'),
        ], $extra);
    }

    /** @return array<string, mixed> */
    protected function datosPadresPreempleo(array $extra = []): array
    {
        return array_merge([
            'convive_con' => 'madre,solo',
            'padre_nombre' => 'Carlos Pérez',
            'padre_vive' => 'no',
            'madre_nombre' => 'María García',
            'madre_vive' => 'si',
            'madre_edad' => '58',
            'madre_direccion' => 'Zona 1, Ciudad de Guatemala',
            'madre_ocupacion' => 'Comerciante',
            'madre_lugar_trabajo' => 'Mercado central',
            'madre_telefono' => '55559876',
        ], $extra);
    }

    /** @return array<string, mixed> */
    protected function datosParejaPreempleo(array $extra = []): array
    {
        return array_merge([
            'vive_con_pareja' => 'si',
            'pareja_tipo_relacion' => 'casado',
            'pareja_nombre' => 'Laura Méndez',
            'pareja_edad' => '32',
            'pareja_telefono' => '55551111',
            'pareja_direccion' => 'Ciudad de Guatemala',
            'pareja_ocupacion' => 'Contadora',
            'pareja_lugar_trabajo' => 'Empresa ABC',
            'pareja_tiempo_relacion' => '5 años',
            'pareja_calidad_relacion' => 'buena',
            'pareja_trabaja' => 'si',
        ], $extra);
    }

    /** @return array<string, mixed> */
    protected function datosHijosPreempleo(array $extra = []): array
    {
        return array_merge([
            'tiene_hijos' => 'si',
            'numero_hijos' => 2,
            'hijos_menores' => 2,
            'hijos_dependientes' => 2,
            'hijos' => [
                [
                    'nombre' => 'Sofía',
                    'edad' => '10',
                    'vive_con_candidato' => 'si',
                    'ocupacion' => 'Estudiante',
                    'telefono' => '55551111',
                ],
                [
                    'nombre' => 'Mateo',
                    'edad' => '7',
                    'vive_con_candidato' => 'si',
                    'ocupacion' => '',
                    'telefono' => '',
                ],
            ],
        ], $extra);
    }

    /** @return array<string, mixed> */
    protected function datosSeccion2Preempleo(array $extra = []): array
    {
        return array_merge($this->datosPadresPreempleo(), [
            'estado_civil_detalle' => 'soltero',
            'vive_con_pareja' => 'no',
            'tiene_hijos' => 'no',
            'tiene_hermanos' => 'no',
            'tuvo_matrimonio_union_hijos' => 'no',
            'personas_hogar' => 4,
            'dependientes_economicos' => 2,
            'tipo_vivienda' => 'familiar',
            'personas_contribuyen_gastos' => 2,
        ], $extra);
    }

    /** @return array<string, mixed> */
    protected function datosHermanosPreempleo(array $extra = []): array
    {
        return array_merge([
            'tiene_hermanos' => 'si',
            'hermanos' => [
                [
                    'nombre' => 'Ana Pérez',
                    'edad' => '25',
                    'direccion' => 'Ciudad de Guatemala',
                    'ocupacion' => 'Enfermera',
                    'telefono' => '55554444',
                ],
            ],
        ], $extra);
    }

    /** @return array<string, string> */
    protected function respuestasIntegridadLaboral(string $respuesta = 'No'): array
    {
        $datos = [];
        foreach (\App\Support\HistorialLaboralIntegridad::PREGUNTAS as $p) {
            $datos[$p['key']] = $respuesta;
        }

        return $datos;
    }

    /** @return array<string, string> */
    protected function respuestasJudiciales(string $respuesta = 'No'): array
    {
        $datos = [];
        foreach (\App\Support\AntecedentesJudiciales::PREGUNTAS as $p) {
            $datos[$p['key']] = $respuesta;
        }

        return $datos;
    }

    /** @return array<string, string> */
    protected function respuestasComplementarias(string $respuesta = 'N/A'): array
    {
        $datos = [];
        foreach (\App\Support\InformacionComplementaria::PREGUNTAS as $p) {
            $datos[$p['key']] = $respuesta;
        }

        return $datos;
    }

    /** @return array<string, mixed> */
    protected function datosSaludHabitosPreempleo(array $extra = []): array
    {
        return array_merge([
            'salud_preocupaciones' => 'Ninguna',
            'salud_estado_general' => 'buena',
            'salud_atencion_psicologica' => 'no',
            'salud_tipo_sangre' => 'O+',
            'salud_peso' => '150',
            'salud_estatura' => '1.75',
            'salud_practica_deporte' => 'si',
            'salud_detalle_deporte' => 'Fútbol',
            'salud_tratamiento_medico' => 'no',
            'salud_hospitalizaciones' => 'no',
            'salud_ausencias_enfermedad' => 'Ninguna',
            'salud_intento_suicidio' => 'No',
            'tiene_tatuajes' => 'no',
            'tiene_perforaciones' => 'no',
            'habito_tiempo_libre' => 'Lectura',
            'habito_bares_frecuencia' => 'Nunca',
            'habito_alcohol_ultimo' => 'N/A',
            'habito_alcohol_mensual' => 'N/A',
            'habito_alcohol_detenido' => 'N/A',
            'habito_alcohol_laboral' => 'N/A',
            'habito_alcohol_despido' => 'N/A',
            'habito_tabaco' => 'No fumo',
            'habito_juegos_azar' => 'Ninguno',
            'sustancias_usadas' => ['ninguna'],
            'sustancia_experiencia' => 'N/A',
            'sustancia_ultima_vez' => 'N/A',
            'sustancia_ultimos_6_meses' => 'N/A',
            'sustancia_familiar_consume' => 'N/A',
            'sustancia_consumo_frente' => 'N/A',
            'sustancia_guardo_transporto' => 'N/A',
            'sustancia_mejora_animo' => 'N/A',
        ], $extra);
    }

    /** @return array<string, mixed> */
    protected function datosEconomicosAmpliadosPreempleo(array $extra = []): array
    {
        return array_merge([
            'econ_es_fiador' => 'no',
            'econ_posee_propiedades' => 'no',
            'econ_posee_vehiculos' => 'no',
            'econ_problemas_bancarios' => 'no',
            'econ_demandas_deudas' => 'no',
            'econ_problemas_sat' => 'no',
            'econ_tipo_vivienda_detalle' => 'Propio',
            'econ_dependientes_detalle' => 'Ninguna',
            'econ_ingresos_adicionales_detalle' => 'No',
            'econ_pretension_salarial' => 8500,
            'econ_gastos_mensuales_aprox' => 6000,
        ], $extra);
    }

    /** @return array<string, mixed> */
    protected function datosSeccion3Preempleo(array $extra = []): array
    {
        return array_merge([
            'ultimo_nivel_academico' => 'ninguno',
            'experiencia_previa' => 'no',
            'situacion_laboral_actual' => 'empleado',
            'anos_experiencia_laboral' => 5,
            'empresa_actual' => 'Empresa Ejemplo S.A.',
            'puesto_actual' => 'Analista',
            'salario_actual' => 8000,
        ], $this->respuestasIntegridadLaboral(), $extra);
    }

    /** @return array<string, mixed> */
    protected function datosSeccion4Preempleo(array $extra = []): array
    {
        return array_merge([
            'tiene_deudas' => 'no',
        ], $this->datosEconomicosAmpliadosPreempleo(), $extra);
    }

    /** @return array<string, mixed> */
    protected function datosExparejaPreempleo(array $extra = []): array
    {
        return array_merge([
            'tuvo_matrimonio_union_hijos' => 'si',
            'expareja_nombre' => 'Ana López',
            'expareja_tipo_relacion' => 'matrimonio',
            'expareja_tiempo_relacion' => '8 años',
            'expareja_hijos_comun' => 'si',
            'expareja_cantidad_hijos' => 1,
            'expareja_problemas_legales' => 'no',
            'expareja_apoyo_economico' => 'no',
        ], $extra);
    }

    /** @return array<string, mixed> */
    protected function datosFormacionAcademicaPreempleo(array $extra = []): array
    {
        $niveles = ['primaria', 'basico', 'diversificado', 'tecnico', 'universitario'];
        $filas = [];

        foreach ($niveles as $nivel) {
            $filas[] = [
                'nivel' => $nivel,
                'estado' => 'completo',
                'carrera' => $nivel === 'universitario' ? 'Administración' : '',
                'institucion' => $nivel === 'universitario' ? 'Universidad de San Carlos' : 'Colegio Ejemplo',
                'anio' => '2015',
                'respaldo' => 'si',
            ];
        }

        return array_merge([
            'ultimo_nivel_academico' => 'universitario',
            'formacion_academica' => $filas,
        ], $extra);
    }

    /** @return array<string, mixed> */
    protected function datosEmpleosPreempleo(array $extra = []): array
    {
        return array_merge([
            'experiencia_previa' => 'si',
            'empleos' => [
                [
                    'empresa' => 'Empresa Anterior S.A.',
                    'puesto' => 'Asistente',
                    'fechas_laboradas' => '2018-01-15 al 2022-06-30',
                    'ultimo_salario' => '4500',
                    'motivo_retiro' => 'Mejor oportunidad',
                ],
            ],
        ], $extra);
    }

    /** @return array<string, mixed> */
    protected function datosDeudasPreempleo(array $extra = []): array
    {
        return array_merge([
            'tiene_deudas' => 'si',
            'deudas' => [
                [
                    'entidad' => 'Banco Industrial',
                    'monto' => '50000',
                    'saldo' => '30000',
                    'cuota' => '1200',
                    'motivo' => 'Préstamo personal',
                    'antiguedad' => '3 años',
                    'estatus' => 'al_dia',
                    'meses_atraso' => '',
                ],
            ],
        ], $extra);
    }

    /** @return array<string, mixed> */
    protected function datosSeccion5Preempleo(array $extra = []): array
    {
        return array_merge(
            $this->datosSaludHabitosPreempleo(),
            $this->respuestasJudiciales(),
            $this->respuestasComplementarias(),
            $extra
        );
    }

    /** Periódica y específica §5: solo aspecto judicial + información adicional. */
    /** @return array<string, mixed> */
    protected function datosSeccion5PeriodicaEspecifica(array $extra = []): array
    {
        return array_merge(
            $this->respuestasJudiciales(),
            ['informacion_adicional_final' => 'Sin información adicional.'],
            $extra
        );
    }
}
