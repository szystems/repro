<?php

namespace App\Support;

use App\Models\Cuestionario;

/** Presentación unificada de cuestionarios en admin REPRO y portal empresa (lectura/edición). */
class CuestionarioPresentacionDashboard
{
    /**
     * @return array<string, mixed>
     */
    public static function respuestasSeccion(Cuestionario $cuestionario, int $numeroSeccion, bool $soloEmpresa = false): array
    {
        $respuestas = $cuestionario->obtenerRespuestasSeccion($numeroSeccion);

        if ($soloEmpresa) {
            return CamposInternosPreempleo::filtrarRespuestasParaEmpresa(
                $respuestas,
                $cuestionario->tipo_formulario ?? 'preempleo'
            );
        }

        return CamposInternosPreempleo::excluirCamposSistema($respuestas);
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    public static function tablasSeccion(Cuestionario $cuestionario, int $numeroSeccion, bool $soloEmpresa = false): array
    {
        $tablas = $cuestionario->getTablasPorNumeroSeccion($numeroSeccion);

        if ($soloEmpresa && ($cuestionario->tipo_formulario ?? '') === 'socioeconomico' && $numeroSeccion === 6) {
            unset($tablas['referencias_vecinales']);
        }

        return $tablas;
    }

    /**
     * @return array<string, int>
     */
    public static function idsRespuestasSeccion(Cuestionario $cuestionario, int $numeroSeccion): array
    {
        $slug = CuestionarioSecciones::slug($numeroSeccion, $cuestionario->tipo_formulario ?? 'preempleo');

        return $cuestionario->respuestas()
            ->where('seccion', $slug)
            ->pluck('id', 'campo')
            ->toArray();
    }

    public static function slugSeccion(Cuestionario $cuestionario, int $numeroSeccion): string
    {
        return CuestionarioSecciones::slug($numeroSeccion, $cuestionario->tipo_formulario ?? 'preempleo');
    }

    /** Periódica y específica: §5 solo aspecto judicial (PDF PERIODICO ESPECIFICO). */
    public static function esSeccion5SoloJudicial(string $tipoFormulario): bool
    {
        return in_array($tipoFormulario, ['periodica', 'especifica'], true);
    }

    /**
     * Bloques de preguntas textarea para mostrar en lectura/edición.
     *
     * @return list<array{titulo: string, badge: ?string, preguntas: list<array{key: string, label: string}>}>
     */
    public static function bloquesPreguntas(int $numeroSeccion, string $tipoFormulario, bool $soloEmpresa = false): array
    {
        if ($soloEmpresa) {
            if ($numeroSeccion === 5 && ! self::esSeccion5SoloJudicial($tipoFormulario)) {
                return [[
                    'titulo' => 'Información complementaria',
                    'badge' => null,
                    'preguntas' => InformacionComplementaria::PREGUNTAS,
                ]];
            }

            return [];
        }

        $bloques = [];

        if ($numeroSeccion === 3 && in_array($tipoFormulario, ['preempleo', 'socioeconomico'], true)) {
            $bloques[] = [
                'titulo' => 'Preguntas de integridad laboral',
                'badge' => 'Confidencial — uso interno REPRO',
                'preguntas' => HistorialLaboralIntegridad::PREGUNTAS,
            ];
        }

        if ($numeroSeccion === 3 && in_array($tipoFormulario, ['periodica', 'especifica'], true)) {
            $preguntas = HistorialLaboralPeriodico::PREGUNTAS;
            if ($tipoFormulario === 'especifica' && isset($preguntas[0])) {
                $preguntas[0] = array_merge($preguntas[0], [
                    'label' => HistorialLaboralPeriodico::labelPregunta1(true),
                ]);
            }
            $bloques[] = [
                'titulo' => 'Preguntas complementarias de empleo actual',
                'badge' => 'Confidencial — uso interno REPRO',
                'preguntas' => $preguntas,
            ];
            $bloques[] = [
                'titulo' => HistorialLaboralPeriodico::CAMPO_INFORMACION_ADICIONAL['label'],
                'badge' => null,
                'preguntas' => [HistorialLaboralPeriodico::CAMPO_INFORMACION_ADICIONAL],
            ];
        }

        if ($numeroSeccion === 5 && ! $soloEmpresa) {
            if (self::esSeccion5SoloJudicial($tipoFormulario)) {
                $bloques[] = [
                    'titulo' => SaludHabitosCampos::TITULO_SALUD,
                    'badge' => 'Confidencial',
                    'preguntas' => SaludHabitosCampos::preguntasAlergiasEmbarazo(),
                ];
                $bloques[] = [
                    'titulo' => AntecedentesJudiciales::TITULO_BLOQUE,
                    'badge' => 'Confidencial',
                    'preguntas' => AntecedentesJudiciales::PREGUNTAS,
                ];
            } else {
                $bloques[] = [
                    'titulo' => SaludHabitosCampos::TITULO_SALUD ?? 'Aspectos de salud',
                    'badge' => 'Confidencial',
                    'preguntas' => self::preguntasSaludHabitos(),
                ];
                $bloques[] = [
                    'titulo' => AntecedentesJudiciales::TITULO_BLOQUE,
                    'badge' => 'Confidencial',
                    'preguntas' => AntecedentesJudiciales::PREGUNTAS,
                ];
                $bloques[] = [
                    'titulo' => InformacionComplementaria::TITULO_BLOQUE,
                    'badge' => null,
                    'preguntas' => InformacionComplementaria::PREGUNTAS,
                ];
            }
        }

        if ($numeroSeccion === 6 && $tipoFormulario === 'socioeconomico') {
            $bloques[] = [
                'titulo' => 'Labor previo en la empresa',
                'badge' => null,
                'preguntas' => [[
                    'key' => 'comp_ha_laborado_empresa',
                    'label' => '¿Ha laborado anteriormente para la empresa contratante?',
                ]],
            ];
        }

        return $bloques;
    }

    /**
     * @return list<array{key: string, titulo: string, metodo: string}>
     */
    public static function tablasConfig(int $numeroSeccion, string $tipoFormulario, bool $soloEmpresa = false): array
    {
        if ($numeroSeccion === 2) {
            $tablas = [
                ['key' => 'hijos', 'titulo' => 'Detalle de hijos', 'metodo' => 'columnasHijos'],
            ];
            if (! in_array($tipoFormulario, ['periodica', 'especifica'], true)) {
                $tablas[] = ['key' => 'hermanos', 'titulo' => 'Detalle de hermanos', 'metodo' => 'columnasHermanos'];
            }

            return $tablas;
        }

        if ($numeroSeccion === 3) {
            if (in_array($tipoFormulario, ['periodica', 'especifica'], true)) {
                $tablas = [
                    ['key' => 'empleo_actual', 'titulo' => 'Empleo actual', 'metodo' => 'columnasEmpleoActualPeriodico'],
                ];
                if ($tipoFormulario === 'periodica') {
                    array_unshift($tablas, [
                        'key' => 'estudios_actuales',
                        'titulo' => 'Estudios actuales',
                        'metodo' => 'columnasEstudiosActuales',
                    ]);
                    array_unshift($tablas, [
                        'key' => 'formacion_academica',
                        'titulo' => 'Formación académica',
                        'metodo' => 'columnasFormacionAcademica',
                    ]);
                }

                return $tablas;
            }

            return [
                ['key' => 'formacion_academica', 'titulo' => 'Formación académica', 'metodo' => 'columnasFormacionAcademica'],
                ['key' => 'estudios_actuales', 'titulo' => 'Estudios actuales', 'metodo' => 'columnasEstudiosActuales'],
                ['key' => 'empleos', 'titulo' => 'Historial de empleos', 'metodo' => 'columnasEmpleosPreempleo'],
            ];
        }

        if ($numeroSeccion === 4) {
            return [
                ['key' => 'deudas', 'titulo' => 'Detalle de deudas', 'metodo' => 'columnasDeudas'],
            ];
        }

        if ($numeroSeccion === 5 && ! $soloEmpresa && ! self::esSeccion5SoloJudicial($tipoFormulario)) {
            return [
                ['key' => 'tatuajes', 'titulo' => 'Tatuajes', 'metodo' => 'columnasTatuajes'],
                ['key' => 'perforaciones', 'titulo' => 'Perforaciones', 'metodo' => 'columnasPerforaciones'],
            ];
        }

        if ($numeroSeccion === 6 && $tipoFormulario === 'socioeconomico') {
            $tablas = [
                ['key' => 'referencias_familiares', 'titulo' => 'Referencias familiares', 'metodo' => 'columnasReferenciasFamiliares'],
                ['key' => 'referencias_personales', 'titulo' => 'Referencias personales', 'metodo' => 'columnasReferenciasPersonales'],
                ['key' => 'referencias_laborales', 'titulo' => 'Referencias laborales', 'metodo' => 'columnasReferenciasLaborales'],
                ['key' => 'bienes', 'titulo' => 'Bienes y pertenencias', 'metodo' => 'columnasBienes'],
                ['key' => 'presupuesto', 'titulo' => 'Presupuesto personal (gastos mensuales)', 'metodo' => 'columnasPresupuesto'],
            ];
            if (! $soloEmpresa) {
                array_splice($tablas, 2, 0, [[
                    'key' => 'referencias_vecinales',
                    'titulo' => 'Referencias vecinales',
                    'metodo' => 'columnasReferenciasVecinales',
                ]]);
            }

            return $tablas;
        }

        return [];
    }

    /**
     * Campos escalares con etiqueta legible (no preguntas ni tablas).
     *
     * @return list<array{key: string, label: string}>
     */
    public static function camposEscalares(int $numeroSeccion, string $tipoFormulario, bool $soloEmpresa = false): array
    {
        $campos = [];

        if ($numeroSeccion === 1) {
            $campos = self::camposDatosPersonales();
        } elseif ($numeroSeccion === 2) {
            $campos = self::camposFamiliares($tipoFormulario);
        } elseif ($numeroSeccion === 3 && in_array($tipoFormulario, ['preempleo', 'socioeconomico'], true)) {
            $campos = self::camposHistorialLaboralPreempleo();
        } elseif ($numeroSeccion === 3 && in_array($tipoFormulario, ['periodica', 'especifica'], true)) {
            $campos = [
                ['key' => 'ultimo_nivel_academico', 'label' => 'Último nivel académico'],
                ['key' => 'tiene_empleo_actual', 'label' => '¿Tiene o ha tenido empleo?'],
            ];
        } elseif ($numeroSeccion === 4) {
            $campos = self::camposEconomicos($tipoFormulario);
        } elseif ($numeroSeccion === 5 && self::esSeccion5SoloJudicial($tipoFormulario)) {
            $campos = [[
                'key' => 'informacion_adicional_final',
                'label' => 'Información adicional',
            ]];
        } elseif ($numeroSeccion === 6 && $tipoFormulario === 'socioeconomico' && ! $soloEmpresa) {
            $campos = self::camposViviendaSocio();
        }

        return $campos;
    }

    /** @return list<string> */
    public static function clavesEnPreguntasOBloques(int $numeroSeccion, string $tipoFormulario, bool $soloEmpresa = false): array
    {
        $claves = [];
        foreach (self::bloquesPreguntas($numeroSeccion, $tipoFormulario, $soloEmpresa) as $bloque) {
            foreach ($bloque['preguntas'] as $p) {
                $claves[] = $p['key'];
            }
        }
        foreach (self::camposEscalares($numeroSeccion, $tipoFormulario, $soloEmpresa) as $c) {
            $claves[] = $c['key'];
        }
        foreach (self::tablasConfig($numeroSeccion, $tipoFormulario, $soloEmpresa) as $t) {
            $claves[] = $t['key'];
        }

        return array_merge($claves, [
            'bienes_total', 'presupuesto_total', 'foto_candidato_existente',
        ]);
    }

    public static function etiquetaCampo(string $campo): string
    {
        foreach (HistorialLaboralIntegridad::PREGUNTAS as $p) {
            if ($p['key'] === $campo) {
                return $p['label'];
            }
        }
        foreach (HistorialLaboralPeriodico::PREGUNTAS as $p) {
            if ($p['key'] === $campo) {
                return $p['label'];
            }
        }
        if ($campo === HistorialLaboralPeriodico::CAMPO_INFORMACION_ADICIONAL['key']) {
            return HistorialLaboralPeriodico::CAMPO_INFORMACION_ADICIONAL['label'];
        }
        foreach (AntecedentesJudiciales::PREGUNTAS as $p) {
            if ($p['key'] === $campo) {
                return $p['label'];
            }
        }
        foreach (InformacionComplementaria::PREGUNTAS as $p) {
            if ($p['key'] === $campo) {
                return $p['label'];
            }
        }
        if ($campo === 'informacion_adicional_final') {
            return 'Información adicional';
        }

        $map = self::mapaEtiquetasEscalares();

        return $map[$campo] ?? ucfirst(str_replace('_', ' ', $campo));
    }

    /** @return list<array{key: string, label: string}> */
    private static function preguntasSaludHabitos(): array
    {
        return [
            ['key' => 'salud_preocupaciones', 'label' => SaludHabitosCampos::LABEL_PREOCUPACIONES],
            ['key' => 'salud_estado_general', 'label' => SaludHabitosCampos::LABEL_ESTADO_GENERAL],
            ['key' => 'salud_atencion_psicologica', 'label' => SaludHabitosCampos::LABEL_PSICOLOGICA],
            ['key' => 'salud_detalle_psicologica', 'label' => 'Detalle atención psicológica'],
            ['key' => 'salud_tipo_sangre', 'label' => SaludHabitosCampos::LABEL_TIPO_SANGRE],
            ['key' => 'salud_peso', 'label' => 'Peso (libras)'],
            ['key' => 'salud_estatura', 'label' => 'Estatura (m)'],
            ['key' => 'salud_practica_deporte', 'label' => SaludHabitosCampos::LABEL_DEPORTE],
            ['key' => 'salud_detalle_deporte', 'label' => 'Detalle deporte'],
            ['key' => 'salud_tratamiento_medico', 'label' => SaludHabitosCampos::LABEL_TRATAMIENTO_MEDICO],
            ['key' => 'salud_detalle_tratamiento', 'label' => 'Detalle tratamiento médico'],
            ['key' => 'salud_hospitalizaciones', 'label' => SaludHabitosCampos::LABEL_HOSPITALIZACIONES],
            ['key' => 'salud_detalle_hospitalizaciones', 'label' => 'Detalle hospitalizaciones'],
            ['key' => 'salud_ausencias_enfermedad', 'label' => SaludHabitosCampos::LABEL_AUSENCIAS_ENFERMEDAD],
            ['key' => 'salud_detalle_ausencias', 'label' => 'Detalle ausencias'],
            ['key' => 'salud_alergias', 'label' => SaludHabitosCampos::LABEL_ALERGIAS],
            ['key' => 'salud_detalle_alergias', 'label' => 'Detalle de alergias'],
            ['key' => 'salud_embarazada', 'label' => SaludHabitosCampos::LABEL_EMBARAZADA],
            ['key' => 'salud_intento_suicidio', 'label' => SaludHabitosCampos::LABEL_SUICIDIO],
            ['key' => 'tiene_tatuajes', 'label' => '¿Tiene tatuajes?'],
            ['key' => 'tiene_perforaciones', 'label' => '¿Tiene perforaciones?'],
            ['key' => 'habito_tiempo_libre', 'label' => 'Tiempo libre / hobbies'],
            ['key' => 'habito_bares_frecuencia', 'label' => 'Frecuencia en bares'],
            ['key' => 'habito_alcohol_ultimo', 'label' => 'Última vez que consumió alcohol'],
            ['key' => 'habito_alcohol_mensual', 'label' => 'Consumo mensual de alcohol'],
            ['key' => 'habito_alcohol_detenido', 'label' => '¿Detenido por alcohol?'],
            ['key' => 'habito_alcohol_laboral', 'label' => 'Alcohol en horario laboral'],
            ['key' => 'habito_alcohol_despido', 'label' => 'Despido por alcohol'],
            ['key' => 'habito_tabaco', 'label' => 'Tabaco'],
            ['key' => 'habito_juegos_azar', 'label' => 'Juegos de azar'],
            ['key' => 'sustancias_usadas', 'label' => 'Sustancias usadas'],
            ['key' => 'sustancia_experiencia', 'label' => 'Experiencia con sustancias'],
            ['key' => 'sustancia_ultima_vez', 'label' => 'Última vez sustancias'],
            ['key' => 'sustancia_ultimos_6_meses', 'label' => 'Sustancias últimos 6 meses'],
            ['key' => 'sustancia_familiar_consume', 'label' => 'Familiares que consumen'],
            ['key' => 'sustancia_consumo_frente', 'label' => 'Consumo frente a otros'],
            ['key' => 'sustancia_guardo_transporto', 'label' => 'Guardó/transportó sustancias'],
            ['key' => 'sustancia_mejora_animo', 'label' => 'Sustancias para mejorar ánimo'],
        ];
    }

    /** @return list<array{key: string, label: string}> */
    private static function camposDatosPersonales(): array
    {
        return [
            ['key' => 'nombres_completos', 'label' => 'Nombres completos'],
            ['key' => 'apellidos_completos', 'label' => 'Apellidos completos'],
            ['key' => 'tipo_identificacion', 'label' => 'Tipo de identificación'],
            ['key' => 'dpi', 'label' => 'Número de identificación'],
            ['key' => 'fecha_nacimiento', 'label' => 'Fecha de nacimiento'],
            ['key' => 'estado_civil', 'label' => 'Estado civil'],
            ['key' => 'nacionalidad', 'label' => 'Nacionalidad'],
            ['key' => 'departamento_nacimiento', 'label' => 'Departamento de nacimiento'],
            ['key' => 'municipio_nacimiento', 'label' => 'Municipio de nacimiento'],
            ['key' => 'direccion_residencia', 'label' => 'Dirección de residencia'],
            ['key' => 'departamento', 'label' => 'Departamento de residencia'],
            ['key' => 'municipio', 'label' => 'Municipio de residencia'],
            ['key' => 'telefono_personal', 'label' => 'Teléfono personal'],
            ['key' => 'telefono_alternativo', 'label' => 'Teléfono alternativo'],
            ['key' => 'email_personal', 'label' => 'Correo electrónico'],
            ['key' => 'igss', 'label' => 'IGSS'],
            ['key' => 'nit', 'label' => 'NIT'],
            ['key' => 'licencia_conducir', 'label' => 'Licencia de conducir'],
            ['key' => 'empresa_solicitante', 'label' => 'Empresa solicitante'],
            ['key' => 'puesto_evaluar', 'label' => 'Puesto a evaluar'],
        ];
    }

    /** @return list<array{key: string, label: string}> */
    private static function camposFamiliares(string $tipoFormulario): array
    {
        $campos = [
            ['key' => 'estado_civil_detalle', 'label' => 'Estado civil (detalle)'],
            ['key' => 'convive_con', 'label' => 'Convive con'],
            ['key' => 'padre_nombre', 'label' => 'Nombre del padre'],
            ['key' => 'padre_vive', 'label' => '¿Padre vive?'],
            ['key' => 'padre_edad', 'label' => 'Edad del padre'],
            ['key' => 'padre_direccion', 'label' => 'Dirección del padre'],
            ['key' => 'padre_ocupacion', 'label' => 'Ocupación del padre'],
            ['key' => 'madre_nombre', 'label' => 'Nombre de la madre'],
            ['key' => 'madre_vive', 'label' => '¿Madre vive?'],
            ['key' => 'madre_edad', 'label' => 'Edad de la madre'],
            ['key' => 'madre_direccion', 'label' => 'Dirección de la madre'],
            ['key' => 'madre_ocupacion', 'label' => 'Ocupación de la madre'],
            ['key' => 'vive_con_pareja', 'label' => '¿Vive con pareja?'],
            ['key' => 'pareja_nombre', 'label' => 'Nombre de la pareja'],
            ['key' => 'pareja_edad', 'label' => 'Edad de la pareja'],
            ['key' => 'tiene_hijos', 'label' => '¿Tiene hijos?'],
            ['key' => 'numero_hijos', 'label' => 'Número de hijos'],
            ['key' => 'tipo_vivienda', 'label' => 'Tipo de vivienda'],
            ['key' => 'tuvo_matrimonio_union_hijos', 'label' => 'Matrimonio/unión previa con hijos'],
            ['key' => 'expareja_nombre', 'label' => 'Nombre expareja'],
        ];

        if (! in_array($tipoFormulario, ['periodica', 'especifica'], true)) {
            $campos[] = ['key' => 'tiene_hermanos', 'label' => '¿Tiene hermanos?'];
        }

        return $campos;
    }

    /** @return list<array{key: string, label: string}> */
    private static function camposHistorialLaboralPreempleo(): array
    {
        return [
            ['key' => 'ultimo_nivel_academico', 'label' => 'Último nivel académico'],
            ['key' => 'experiencia_previa', 'label' => '¿Experiencia laboral previa?'],
            ['key' => 'situacion_laboral_actual', 'label' => 'Situación laboral actual'],
            ['key' => 'anos_experiencia_laboral', 'label' => 'Años de experiencia'],
            ['key' => 'empresa_actual', 'label' => 'Empresa actual'],
            ['key' => 'puesto_actual', 'label' => 'Puesto actual'],
            ['key' => 'salario_actual', 'label' => 'Salario actual'],
            ['key' => 'motivo_busqueda', 'label' => 'Motivo de búsqueda de empleo'],
        ];
    }

    /** @return list<array{key: string, label: string}> */
    private static function camposEconomicos(string $tipoFormulario): array
    {
        $campos = [
            ['key' => 'tiene_deudas', 'label' => '¿Tiene deudas?'],
            ['key' => 'econ_es_fiador', 'label' => SituacionEconomicaCampos::LABEL_ES_FIADOR],
            ['key' => 'econ_detalle_es_fiador', 'label' => 'Detalle fiador'],
            ['key' => 'econ_problemas_bancarios', 'label' => SituacionEconomicaCampos::LABEL_PROBLEMAS_BANCARIOS],
            ['key' => 'econ_detalle_problemas_bancarios', 'label' => 'Detalle problemas bancarios'],
            ['key' => 'personas_hogar', 'label' => 'Personas en el hogar'],
            ['key' => 'dependientes_economicos', 'label' => 'Dependientes económicos'],
            ['key' => 'econ_tipo_vivienda_detalle', 'label' => SituacionEconomicaCampos::LABEL_VIVIENDA],
            ['key' => 'econ_dependientes_detalle', 'label' => SituacionEconomicaCampos::LABEL_DEPENDIENTES],
            ['key' => 'econ_ingresos_adicionales_detalle', 'label' => SituacionEconomicaCampos::LABEL_INGRESOS_ADICIONALES],
            ['key' => 'econ_posee_propiedades', 'label' => SituacionEconomicaCampos::LABEL_PROPIEDADES],
            ['key' => 'econ_detalle_propiedades', 'label' => 'Detalle propiedades'],
            ['key' => 'econ_posee_vehiculos', 'label' => SituacionEconomicaCampos::LABEL_VEHICULOS],
            ['key' => 'econ_detalle_vehiculos', 'label' => 'Detalle vehículos'],
            ['key' => 'econ_demandas_deudas', 'label' => SituacionEconomicaCampos::LABEL_DEMANDAS],
            ['key' => 'econ_detalle_demandas', 'label' => 'Detalle demandas'],
            ['key' => 'econ_pretension_salarial', 'label' => SituacionEconomicaCampos::LABEL_PRETENSION],
            ['key' => 'econ_gastos_mensuales_aprox', 'label' => SituacionEconomicaCampos::LABEL_GASTOS_MENSUALES],
            ['key' => 'econ_problemas_sat', 'label' => SituacionEconomicaCampos::LABEL_SAT],
            ['key' => 'econ_detalle_sat', 'label' => 'Detalle SAT'],
        ];

        if ($tipoFormulario === 'socioeconomico') {
            $campos[] = ['key' => 'econ_patrimonio_aprox', 'label' => SituacionEconomicaCampos::LABEL_PATRIMONIO_SOCIO];
        }

        return $campos;
    }

    /** @return list<array{key: string, label: string}> */
    private static function camposViviendaSocio(): array
    {
        return [
            ['key' => 'viv_tiempo_residencia', 'label' => 'Tiempo en domicilio actual'],
            ['key' => 'viv_tipo_vivienda_detalle', 'label' => 'Tipo de vivienda (propia/alquilada)'],
            ['key' => 'viv_propietario', 'label' => 'Propietario'],
            ['key' => 'viv_monto_alquiler', 'label' => 'Monto alquiler'],
            ['key' => 'viv_habitantes_detalle', 'label' => 'Habitantes y parentesco'],
            ['key' => 'viv_refs_ubicacion', 'label' => 'Referencias de ubicación'],
            ['key' => 'viv_zona_riesgo', 'label' => '¿Zona de riesgo?'],
            ['key' => 'viv_detalle_zona_riesgo', 'label' => 'Detalle zona de riesgo'],
        ];
    }

    /** @return array<string, string> */
    private static function mapaEtiquetasEscalares(): array
    {
        $map = [];
        foreach (self::camposDatosPersonales() as $c) {
            $map[$c['key']] = $c['label'];
        }
        foreach (self::camposHistorialLaboralPreempleo() as $c) {
            $map[$c['key']] = $c['label'];
        }
        foreach (self::camposViviendaSocio() as $c) {
            $map[$c['key']] = $c['label'];
        }

        return $map;
    }
}
