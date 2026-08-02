<?php

namespace Database\Seeders\Support;

use App\Models\Cuestionario;
use App\Models\CuestionarioRespuesta;
use App\Models\EvaluadoOrden;
use App\Support\AntecedentesJudiciales;
use App\Support\CuestionarioSecciones;
use App\Support\HistorialLaboralPeriodico;
use App\Support\InformacionComplementaria;
use App\Support\SocioeconomicoComplementariaCampos;
use Illuminate\Support\Facades\Storage;

/** Pobla cuestionarios completos para pruebas de export Word. */
class DemoWordCuestionarioBuilder
{
    /** @param array{0: int, 1: int, 2: int} $colorRgb */
    public static function poblar(EvaluadoOrden $evaluado, array $colorRgb = [70, 130, 180]): Cuestionario
    {
        if ($evaluado->cuestionario) {
            $evaluado->cuestionario->respuestas()->delete();
            $evaluado->cuestionario->delete();
        }

        $tipoCuestionario = $evaluado->tipoFormularioCuestionario();
        $totalSecciones = Cuestionario::totalSeccionesParaTipo($tipoCuestionario);

        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => $tipoCuestionario,
            'seccion_actual' => $totalSecciones,
            'total_secciones' => $totalSecciones,
            'progreso_porcentaje' => 100,
            'completado' => true,
            'bloqueado' => false,
            'instrucciones_leidas_at' => now()->subDays(2),
            'acepta_terminos' => true,
            'acepta_terminos_at' => now()->subDays(2),
            'acepta_infornet' => true,
            'acepta_infornet_at' => now()->subDays(2),
            'completado_at' => now()->subDay(),
        ]);

        self::guardarFoto($cuestionario, $colorRgb);
        self::guardarSeccion1($cuestionario, $evaluado);
        self::guardarSeccion2($cuestionario, $tipoCuestionario);
        self::guardarSeccion3($cuestionario, $evaluado, $tipoCuestionario);
        self::guardarSeccion4($cuestionario, $tipoCuestionario);
        self::guardarSeccion5($cuestionario, $tipoCuestionario);

        if ($tipoCuestionario === 'socioeconomico') {
            self::guardarSeccion6Socio($cuestionario);
        }

        return $cuestionario;
    }

    /** @param array{0: int, 1: int, 2: int} $colorRgb */
    private static function guardarFoto(Cuestionario $cuestionario, array $colorRgb): void
    {
        $slug = CuestionarioSecciones::slug(1, $cuestionario->tipo_formulario);
        $extension = 'jpg';
        $bytes = self::generarImagenPrueba($colorRgb, 'jpeg');
        if ($bytes === null) {
            $bytes = self::generarImagenPrueba($colorRgb, 'png');
            $extension = 'png';
        }
        if ($bytes === null) {
            $bytes = self::jpegMinimo();
            $extension = 'jpg';
        }

        $directorio = "cuestionarios/fotos/{$cuestionario->id}";
        $ruta = "{$directorio}/foto_candidato.{$extension}";

        Storage::disk('local')->put($ruta, $bytes);

        CuestionarioRespuesta::updateOrCreate(
            [
                'cuestionario_id' => $cuestionario->id,
                'seccion' => $slug,
                'campo' => 'foto_candidato',
            ],
            [
                'valor' => $ruta,
                'tipo_campo' => 'file',
                'requerido' => true,
            ]
        );
    }

    private static function guardarSeccion1(Cuestionario $cuestionario, EvaluadoOrden $evaluado): void
    {
        $slug = CuestionarioSecciones::slug(1, $cuestionario->tipo_formulario);

        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, $slug, [
            'nombres_completos' => $evaluado->nombre,
            'apellidos_completos' => $evaluado->apellidos,
            'tipo_identificacion' => 'dpi',
            'dpi' => $evaluado->dpi,
            'fecha_nacimiento' => '1990-08-12',
            'estado_civil' => 'casado',
            'nacionalidad' => 'Guatemala',
            'departamento_nacimiento' => 'Quetzaltenango',
            'municipio_nacimiento' => 'Quetzaltenango',
            'direccion_residencia' => '3ra calle 4-56 zona 3, Quetzaltenango',
            'departamento' => 'Quetzaltenango',
            'municipio' => 'Quetzaltenango',
            'telefono_personal' => '77654321',
            'email_personal' => $evaluado->email,
            'licencia_conducir' => 'si',
            'edad' => '35',
        ]);
    }

    private static function guardarSeccion2(Cuestionario $cuestionario, string $tipoCuestionario): void
    {
        $slug = CuestionarioSecciones::slug(2, $tipoCuestionario);

        $datos = [
            'convive_con' => 'pareja,hijos',
            'padre_nombre' => 'Mauricio López',
            'padre_vive' => 'si',
            'padre_edad' => '62',
            'padre_ocupacion' => 'Agricultor',
            'padre_telefono' => '55511222',
            'madre_nombre' => 'Rosa López',
            'madre_vive' => 'si',
            'madre_edad' => '58',
            'madre_ocupacion' => 'Comerciante',
            'madre_telefono' => '55533444',
            'vive_con_pareja' => 'si',
            'pareja_tipo_relacion' => 'casado',
            'pareja_nombre' => 'Laura Méndez',
            'pareja_edad' => '33',
            'pareja_telefono' => '55555666',
            'pareja_direccion' => '12 av. 3-45 zona 1, Quetzaltenango',
            'pareja_ocupacion' => 'Contadora',
            'pareja_lugar_trabajo' => 'Finanzas del Norte S.A.',
            'pareja_tiempo_relacion' => '8 años',
            'pareja_calidad_relacion' => 'buena',
            'tiene_hijos' => 'si',
            'numero_hijos' => 2,
            'tuvo_matrimonio_union_hijos' => 'no',
            'personas_hogar' => 4,
            'dependientes_economicos' => 2,
            'tipo_vivienda' => 'propia',
        ];

        if (! in_array($tipoCuestionario, ['periodica', 'especifica'], true)) {
            $datos['tiene_hermanos'] = 'si';
        }

        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, $slug, $datos);

        CuestionarioRespuesta::guardarTabla($cuestionario->id, $slug, 'hijos', [
            [
                'nombre' => 'Sofía López',
                'edad' => '10',
                'vive_con_candidato' => 'si',
                'ocupacion' => 'Estudiante',
                'telefono' => '',
            ],
            [
                'nombre' => 'Mateo López',
                'edad' => '7',
                'vive_con_candidato' => 'si',
                'ocupacion' => '',
                'telefono' => '',
            ],
        ]);

        if (! in_array($tipoCuestionario, ['periodica', 'especifica'], true)) {
            CuestionarioRespuesta::guardarTabla($cuestionario->id, $slug, 'hermanos', [
                [
                    'nombre' => 'Ana López',
                    'edad' => '28',
                    'direccion' => 'Quetzaltenango',
                    'ocupacion' => 'Enfermera',
                    'telefono' => '55577889',
                ],
            ]);
        }
    }

    private static function guardarSeccion3(Cuestionario $cuestionario, EvaluadoOrden $evaluado, string $tipoCuestionario): void
    {
        $slug = CuestionarioSecciones::slug(3, $tipoCuestionario);

        if (in_array($tipoCuestionario, ['periodica', 'especifica'], true)) {
            CuestionarioRespuesta::guardarRespuestas($cuestionario->id, $slug, self::respuestasPeriodicas($evaluado, $tipoCuestionario));

            CuestionarioRespuesta::guardarTabla($cuestionario->id, $slug, 'formacion_academica', [
                [
                    'nivel' => 'universitario',
                    'estado' => 'completo',
                    'carrera' => 'Administración de Empresas',
                    'institucion' => 'Universidad Rafael Landívar',
                    'anio' => '2014',
                    'respaldo' => 'si',
                ],
            ]);

            CuestionarioRespuesta::guardarTabla($cuestionario->id, $slug, 'empleo_actual', [
                [
                    'empresa' => 'Distribuidora La Central S.A.',
                    'puesto' => $evaluado->puesto_evaluar ?: 'Supervisor de bodega',
                    'fechas_laboradas' => '2019-03-01 al Actual',
                    'salario_actual' => '6500',
                    'motivo_prueba' => 'Evaluación periódica de confianza',
                ],
            ]);

            return;
        }

        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, $slug, [
            'ultimo_nivel_academico' => 'universitario',
            'experiencia_previa' => 'si',
            'situacion_laboral_actual' => 'empleado',
            'anos_experiencia_laboral' => 8,
            'empresa_actual' => 'Distribuidora La Central S.A.',
            'puesto_actual' => $evaluado->puesto_evaluar ?: 'Analista',
            'salario_actual' => 7500,
        ]);

        CuestionarioRespuesta::guardarTabla($cuestionario->id, $slug, 'formacion_academica', [
            ['nivel' => 'primaria', 'estado' => 'completo', 'carrera' => '', 'institucion' => 'Escuela Oficial Urbana', 'anio' => '2002', 'respaldo' => 'si'],
            ['nivel' => 'basico', 'estado' => 'completo', 'carrera' => '', 'institucion' => 'Colegio Básico Central', 'anio' => '2008', 'respaldo' => 'si'],
            ['nivel' => 'diversificado', 'estado' => 'completo', 'carrera' => 'Ciencias y Letras', 'institucion' => 'Liceo Bilingüe', 'anio' => '2010', 'respaldo' => 'si'],
            ['nivel' => 'universitario', 'estado' => 'completo', 'carrera' => 'Administración de Empresas', 'institucion' => 'Universidad Rafael Landívar', 'anio' => '2014', 'respaldo' => 'si'],
        ]);

        CuestionarioRespuesta::guardarTabla($cuestionario->id, $slug, 'empleos', [
            [
                'empresa' => 'Comercial El Progreso',
                'puesto' => 'Auxiliar administrativo',
                'fechas_laboradas' => '2015-01-10 al 2018-12-20',
                'ultimo_salario' => '4200',
                'motivo_retiro' => 'Mejor oportunidad laboral',
            ],
            [
                'empresa' => 'Distribuidora La Central S.A.',
                'puesto' => $evaluado->puesto_evaluar ?: 'Analista de inventarios',
                'fechas_laboradas' => '2019-03-01 al Actual',
                'ultimo_salario' => '7500',
                'motivo_retiro' => 'Empleo actual',
            ],
        ]);
    }

    private static function guardarSeccion4(Cuestionario $cuestionario, string $tipoCuestionario): void
    {
        $slug = CuestionarioSecciones::slug(4, $tipoCuestionario);

        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, $slug, array_merge([
            'tiene_deudas' => 'si',
            'econ_es_fiador' => 'no',
            'econ_tipo_vivienda_detalle' => 'Propio',
            'econ_dependientes_detalle' => '1, mi hija',
            'econ_ingresos_adicionales_detalle' => 'No',
            'econ_posee_propiedades' => 'no',
            'econ_posee_vehiculos' => 'si',
            'econ_detalle_vehiculos' => 'Motocicleta Honda 2020',
            'econ_problemas_bancarios' => 'no',
            'econ_demandas_deudas' => 'no',
            'econ_pretension_salarial' => 7500,
            'econ_gastos_mensuales_aprox' => 5500,
            'econ_problemas_sat' => 'no',
        ], $tipoCuestionario === 'socioeconomico' ? [
            'econ_patrimonio_aprox' => 'Q 85,000 en vehículo y electrodomésticos.',
        ] : []));

        CuestionarioRespuesta::guardarTabla($cuestionario->id, $slug, 'deudas', [
            [
                'entidad' => 'Banco Industrial',
                'monto' => '45000',
                'saldo' => '28000',
                'cuota' => '1150',
                'motivo' => 'Préstamo personal',
                'antiguedad' => '3 años',
                'estatus' => 'al_dia',
                'meses_atraso' => '',
            ],
        ]);
    }

    private static function guardarSeccion5(Cuestionario $cuestionario, string $tipoCuestionario): void
    {
        $slug = CuestionarioSecciones::slug(5, $tipoCuestionario);

        $datos = [
            'salud_preocupaciones' => 'Ninguna relevante',
            'salud_estado_general' => 'buena',
            'salud_atencion_psicologica' => 'no',
            'salud_tipo_sangre' => 'O+',
            'salud_peso' => '165',
            'salud_estatura' => '1.72',
            'salud_practica_deporte' => 'si',
            'salud_detalle_deporte' => 'Fútbol los fines de semana.',
            'salud_tratamiento_medico' => 'no',
            'salud_hospitalizaciones' => 'no',
            'salud_ausencias_enfermedad' => 'Ninguna',
            'salud_intento_suicidio' => 'No',
            'habito_tiempo_libre' => 'Deportes y lectura',
            'habito_bares_frecuencia' => 'Ocasionalmente',
            'habito_alcohol_ultimo' => 'Hace un mes',
            'habito_alcohol_mensual' => '1-2 veces',
            'habito_alcohol_detenido' => 'Nunca',
            'habito_alcohol_laboral' => 'No',
            'habito_alcohol_despido' => 'No',
            'habito_tabaco' => 'No fumo',
            'habito_juegos_azar' => 'Ninguno',
            'sustancias_usadas' => ['ninguna'],
            'sustancia_experiencia' => 'Niega consumo de sustancias ilegales.',
            'sustancia_ultima_vez' => 'N/A',
            'sustancia_ultimos_6_meses' => 'N/A',
            'sustancia_familiar_consume' => 'N/A',
            'sustancia_consumo_frente' => 'N/A',
            'sustancia_guardo_transporto' => 'N/A',
            'sustancia_mejora_animo' => 'N/A',
            'tiene_tatuajes' => 'no',
            'tiene_perforaciones' => 'no',
        ];

        foreach (AntecedentesJudiciales::PREGUNTAS as $pregunta) {
            $datos[$pregunta['key']] = 'No. Sin antecedentes ni situaciones reportables (demo Word).';
        }

        foreach (InformacionComplementaria::PREGUNTAS as $pregunta) {
            $datos[$pregunta['key']] = match ($pregunta['key']) {
                'comp_licencia_conducir' => 'Licencia tipo C vigente.',
                'comp_sindicato' => 'No he pertenecido a sindicatos.',
                'comp_familiar_empresa' => 'No tengo familiares en la empresa solicitante.',
                'comp_como_se_entero' => 'Portal de empleos y referido interno.',
                'comp_condiciones_laborales' => 'De acuerdo con las condiciones ofrecidas.',
                'comp_metas' => 'Estabilidad laboral y crecimiento profesional a mediano plazo.',
                'comp_cualidades_defectos' => 'Responsable, puntual; debe mejorar delegación.',
                'comp_redes_usuario' => '@demo_candidato en Facebook e Instagram.',
                default => 'N/A para demo Word.',
            };
        }

        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, $slug, $datos);
    }

    private static function guardarSeccion6Socio(Cuestionario $cuestionario): void
    {
        $slug = CuestionarioSecciones::slug(6, 'socioeconomico');

        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, $slug, [
            'viv_tiempo_residencia' => '6 años',
            'viv_tipo_vivienda_detalle' => 'Propia',
            'viv_habitantes_detalle' => 'Cuatro personas: pareja e hijos',
            'viv_refs_ubicacion' => 'Colonia El Progreso, frente al parque',
            'viv_zona_riesgo' => 'no',
            'presupuesto_total' => '8500',
            'bienes_total' => '85000',
            'comp_ha_laborado_empresa' => 'No he laborado anteriormente para esta empresa.',
        ]);

        CuestionarioRespuesta::guardarTabla($cuestionario->id, $slug, 'referencias_familiares', [
            ['nombre' => 'Rosa López', 'parentesco' => 'Tía', 'telefono' => '50255511222', 'direccion' => 'Quetzaltenango', 'lugar_trabajo' => 'Comercio'],
            ['nombre' => 'Mauricio López', 'parentesco' => 'Tío', 'telefono' => '50255533444', 'direccion' => 'Quetzaltenango', 'lugar_trabajo' => 'Albañilería'],
            ['nombre' => 'Elena Ruiz', 'parentesco' => 'Prima', 'telefono' => '50255555666', 'direccion' => 'Xela', 'lugar_trabajo' => 'Oficina'],
        ]);

        CuestionarioRespuesta::guardarTabla($cuestionario->id, $slug, 'referencias_personales', [
            ['nombre' => 'Pedro Ramírez', 'relacion' => 'Compañero de trabajo', 'telefono' => '50255566778', 'anos_conocerlo' => '8'],
            ['nombre' => 'María Castillo', 'relacion' => 'Vecina', 'telefono' => '50255599001', 'anos_conocerlo' => '5'],
            ['nombre' => 'Jorge Méndez', 'relacion' => 'Amigo de la universidad', 'telefono' => '50255588123', 'anos_conocerlo' => '6'],
        ]);

        CuestionarioRespuesta::guardarTabla($cuestionario->id, $slug, 'bienes', [
            ['descripcion' => 'Motocicleta Honda 2020', 'valor' => '25000'],
            ['descripcion' => 'Electrodomésticos', 'valor' => '15000'],
        ]);

        CuestionarioRespuesta::guardarTabla($cuestionario->id, $slug, 'presupuesto', array_map(
            fn (array $fila, int $i) => ['concepto' => $fila['concepto'], 'monto' => (string) (800 + ($i * 100))],
            SocioeconomicoComplementariaCampos::filasPresupuestoIniciales(),
            array_keys(SocioeconomicoComplementariaCampos::filasPresupuestoIniciales())
        ));
    }

    /** @return array<string, string> */
    private static function respuestasPeriodicas(EvaluadoOrden $evaluado, string $tipoCuestionario): array
    {
        $respuestas = [];

        foreach (HistorialLaboralPeriodico::PREGUNTAS as $indice => $pregunta) {
            if ($indice === 0 && $tipoCuestionario === 'especifica') {
                $respuestas[$pregunta['key']] = 'Hecho evaluado (demo): el ' . now()->subMonths(2)->format('d/m/Y')
                    . ' se reportó una discrepancia en el arqueo de caja chica por Q 850.00. '
                    . 'El evaluado colaboró en la investigación interna y no se determinó responsabilidad directa.';
            } else {
                $respuestas[$pregunta['key']] = 'Respuesta demo pregunta ' . ($indice + 1)
                    . '. Información laboral de prueba para exportación Word.';
            }
        }

        $respuestas[HistorialLaboralPeriodico::CAMPO_INFORMACION_ADICIONAL['key']] =
            'No tengo información adicional relevante para agregar en este momento.';

        return $respuestas;
    }

    /** @param array{0: int, 1: int, 2: int} $colorRgb */
    private static function generarImagenPrueba(array $colorRgb, string $formato): ?string
    {
        if (! function_exists('imagecreatetruecolor')) {
            return null;
        }

        $ancho = 300;
        $alto = 400;
        $imagen = imagecreatetruecolor($ancho, $alto);
        $fondo = imagecolorallocate($imagen, $colorRgb[0], $colorRgb[1], $colorRgb[2]);
        imagefill($imagen, 0, 0, $fondo);

        $claro = imagecolorallocate($imagen, 255, 255, 255);
        imagefilledellipse($imagen, (int) ($ancho / 2), 120, 140, 140, $claro);
        imagefilledrectangle($imagen, 70, 210, 230, 380, $claro);

        $texto = imagecolorallocate($imagen, $colorRgb[0], $colorRgb[1], $colorRgb[2]);
        imagestring($imagen, 5, 95, 340, 'REPRO DEMO', $texto);

        ob_start();
        if ($formato === 'jpeg' && function_exists('imagejpeg')) {
            imagejpeg($imagen, null, 90);
        } elseif ($formato === 'png' && function_exists('imagepng')) {
            imagepng($imagen);
        } else {
            ob_end_clean();
            imagedestroy($imagen);

            return null;
        }

        $bytes = ob_get_clean();
        imagedestroy($imagen);

        return $bytes !== false ? $bytes : null;
    }

    private static function jpegMinimo(): string
    {
        return base64_decode(
            '/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////2wBDAf//////////////////////////////////////////////////////////////////////////////////////wAARCAABAAEDAREAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAn/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCwAA8A/9k='
        );
    }
}
