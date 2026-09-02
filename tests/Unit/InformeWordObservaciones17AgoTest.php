<?php

namespace Tests\Unit;

use App\Models\Cuestionario;
use App\Models\CuestionarioRespuesta;
use App\Models\EvaluadorNota;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Support\CuestionarioSecciones;
use App\Support\InformeWordBloquesEvaluador;
use App\Support\InformeWordExport;
use App\Support\InformeWordXml;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

/**
 * Observaciones del cliente 17-08-2026 sobre el formato de las tablas del informe Word: datos
 * familiares desplazados a la fila de encabezados, fecha del encabezado fuera de su celda,
 * narrativa del aspecto económico en una franja angosta, pareja/expareja y teléfono de emergencia
 * que no se reflejaban, y observaciones de la orden apareciendo en la primera hoja.
 */
class InformeWordObservaciones17AgoTest extends TestCase
{
    use RefreshDatabase;

    public function test_datos_familiares_conserva_los_encabezados_y_ubica_a_cada_progenitor(): void
    {
        $tabla = $this->tablaGenerada('DATOS FAMILIARES');
        $filas = InformeWordXml::filasTabla($tabla);

        $this->assertStringContainsString(
            'Ocupación o lugar de trabajo:',
            InformeWordXml::textoFila($filas[1]),
            'La fila de encabezados de columnas no debe sobrescribirse con los datos del padre'
        );

        $filaPadre = InformeWordXml::textoFila($filas[2]);
        $this->assertStringContainsString('Padre:', $filaPadre);
        $this->assertStringContainsString('Mauricio López', $filaPadre);
        $this->assertStringContainsString('62 años', $filaPadre);
        $this->assertStringContainsString('Agricultor', $filaPadre);

        $filaMadre = InformeWordXml::textoFila($filas[3]);
        $this->assertStringContainsString('Madre:', $filaMadre);
        $this->assertStringContainsString('Rosa López', $filaMadre);
        $this->assertStringContainsString('Comerciante', $filaMadre);
    }

    public function test_procedimiento_inicia_en_nueva_pagina_para_no_traslapar_terminos(): void
    {
        $documentXml = $this->documentXml($this->evaluadoCompleto());

        $this->assertStringContainsString('PROCEDIMIENTO DE EVALUACIÓN', $documentXml);

        $posicion = strpos($documentXml, 'PROCEDIMIENTO DE EVALUACIÓN');
        $this->assertNotFalse($posicion);

        $this->assertStringContainsString(
            'w:pageBreakBefore',
            substr($documentXml, 0, $posicion),
            'PROCEDIMIENTO debe iniciar en página nueva para no invadir el pie de términos'
        );
    }

    public function test_datos_familiares_repara_borde_izquierdo_superior(): void
    {
        $tabla = $this->tablaGenerada('DATOS FAMILIARES');
        $filas = InformeWordXml::filasTabla($tabla);

        foreach ([1, 2] as $indice) {
            $celdas = InformeWordXml::celdasFila($filas[$indice]);
            $this->assertNotEmpty($celdas, "La fila {$indice} debe existir");
            $this->assertMatchesRegularExpression(
                '/<w:left w:val="single"/',
                $celdas[0],
                "La celda de etiqueta en fila {$indice} debe tener borde izquierdo visible"
            );
        }
    }

    public function test_direccion_de_padres_llega_al_informe(): void
    {
        $tabla = $this->tablaGenerada('DATOS FAMILIARES');
        $filas = InformeWordXml::filasTabla($tabla);

        $this->assertStringContainsString('9a avenida 2-15 zona 4', InformeWordXml::textoFila($filas[2]));
        $this->assertStringContainsString('Cantón Chuisuc', InformeWordXml::textoFila($filas[3]));
    }

    public function test_fecha_del_encabezado_queda_en_la_celda_siguiente_a_su_etiqueta(): void
    {
        $tabla = $this->tablaGenerada('Agencia/Sede:');
        $filas = InformeWordXml::filasTabla($tabla);
        $celdas = InformeWordXml::celdasFila($filas[1]);

        $indiceEtiqueta = null;
        foreach ($celdas as $indice => $celda) {
            if (str_contains(InformeWordXml::textoCelda($celda), 'Fecha:')) {
                $indiceEtiqueta = $indice;
                break;
            }
        }

        $this->assertNotNull($indiceEtiqueta, 'La plantilla debe conservar la etiqueta Fecha:');
        $this->assertMatchesRegularExpression(
            '#\d{2}/\d{2}/\d{4}#',
            InformeWordXml::textoCelda($celdas[$indiceEtiqueta + 1]),
            'La fecha debe escribirse junto a la etiqueta y no en la última celda de la fila'
        );
    }

    public function test_observaciones_de_la_primera_hoja_usa_la_nota_del_evaluador(): void
    {
        $evaluado = $this->evaluadoCompleto();
        EvaluadorNota::guardarNota(
            $evaluado->id,
            InformeWordBloquesEvaluador::NOTA_OBSERVACIONES,
            '',
            'Se recomienda validar la constancia de estudios.',
            null
        );

        $tabla = $this->tablaGenerada('OBSERVACIONES', $evaluado);

        $this->assertStringContainsString('Se recomienda validar la constancia de estudios.', $tabla);
        $this->assertStringNotContainsString('Analizar tatuajes del evaluado', $tabla);
    }

    public function test_observaciones_queda_en_blanco_sin_nota_del_evaluador(): void
    {
        $tabla = $this->tablaGenerada('OBSERVACIONES');
        $filas = InformeWordXml::filasTabla($tabla);

        $this->assertSame('', trim(InformeWordXml::textoFila($filas[1])));
        $this->assertStringNotContainsString('Analizar tatuajes del evaluado', $tabla);
        $this->assertStringNotContainsString('—', $tabla);
    }

    public function test_direccion_incluye_municipio_y_departamento(): void
    {
        $tabla = $this->tablaGenerada('DATOS GENERALES');

        $this->assertStringContainsString('13 calle 0-137 zona 6', $tabla);
        $this->assertStringContainsString('Salcajá', $tabla);
        $this->assertStringContainsString('Quetzaltenango', $tabla);
    }

    public function test_telefono_de_emergencia_se_toma_del_formulario(): void
    {
        $tabla = $this->tablaGenerada('DATOS GENERALES');

        $this->assertStringContainsString('5054 5599', $tabla, 'El campo se guarda como telefono_alternativo');
    }

    public function test_datos_de_pareja_se_reflejan_en_estado_civil(): void
    {
        $tabla = $this->tablaGenerada('ESTADO CIVIL');

        $this->assertStringContainsString('Laura Méndez', $tabla);
        $this->assertStringContainsString('33 años', $tabla);
        $this->assertStringContainsString('55555666', $tabla);
        $this->assertStringContainsString('Contadora', $tabla);
        $this->assertStringContainsString('8 años', $tabla);
        $this->assertStringNotContainsString('Número de relación:', $tabla);
    }

    public function test_estado_civil_sin_pareja_no_deja_etiquetas_en_blanco(): void
    {
        $evaluado = $this->evaluadoCompleto([
            'vive_con_pareja' => 'no',
            'pareja_nombre' => '',
        ], seccion: 2);

        $tabla = $this->tablaGenerada('ESTADO CIVIL', $evaluado);

        $this->assertStringContainsString('No aplica', $tabla);
        $this->assertStringNotContainsString('Tiempo de la relación:', $tabla);
        $this->assertStringNotContainsString('—', $tabla);
    }

    public function test_datos_de_expareja_se_reflejan_en_el_informe(): void
    {
        $tabla = $this->tablaGenerada('DATOS DE EXPAREJAS');

        $this->assertStringContainsString('Juan Pérez Coyoy', $tabla);
        $this->assertStringContainsString('Tiempo de relación: 4 años', $tabla);
        $this->assertStringNotContainsString('xxxxxxx', $tabla);
    }

    public function test_narrativa_del_aspecto_economico_ocupa_el_ancho_de_la_tabla(): void
    {
        $tabla = $this->tablaGenerada('ASPECTO ECONÓMICO');
        $filas = InformeWordXml::filasTabla($tabla);

        $anchoCabecera = $this->anchoPrimeraCelda($filas[0]);
        $filaNarrativa = null;
        foreach ($filas as $fila) {
            if (str_contains(InformeWordXml::textoFila($fila), 'Motocicleta')) {
                $filaNarrativa = $fila;
                break;
            }
        }

        $this->assertNotNull($filaNarrativa, 'La narrativa económica debe llegar al informe');

        $celdas = InformeWordXml::celdasFila($filaNarrativa);
        $this->assertCount(1, $celdas, 'La narrativa va en una sola celda combinada');
        $this->assertStringContainsString('w:gridSpan', $celdas[0], 'Debe conservarse la combinación de columnas');
        $this->assertGreaterThan(
            (int) ($anchoCabecera * 0.9),
            $this->anchoPrimeraCelda($filaNarrativa),
            'La narrativa quedaba en una franja angosta mientras la cabecera ocupaba toda la página'
        );
    }

    public function test_estudia_actualmente_indica_respuesta_institucion_y_horario(): void
    {
        $tabla = $this->tablaGenerada('NIVEL ACADÉMICO');

        $filaEstudia = null;
        foreach (InformeWordXml::filasTabla($tabla) as $fila) {
            if (str_contains(InformeWordXml::textoFila($fila), 'Estudia Actualmente:')) {
                $filaEstudia = $fila;
                break;
            }
        }

        $this->assertNotNull($filaEstudia);
        $celdas = InformeWordXml::celdasFila($filaEstudia);

        $respuesta = InformeWordXml::textoCelda($celdas[1]);
        $this->assertStringStartsWith('Sí', $respuesta, 'Primero debe leerse si estudia o no');
        $this->assertStringContainsString('Contaduría Pública y Auditoría', $respuesta);
        $this->assertStringContainsString('Universidad de San Carlos', $respuesta);
        $this->assertStringContainsString('martes y jueves', InformeWordXml::textoCelda($celdas[3]));

        preg_match('/<w:tcW\b[^>]*w:w="(\d+)"/', $celdas[1], $ancho);
        $this->assertGreaterThan(
            2500,
            (int) ($ancho[1] ?? 0),
            'La celda del detalle quedaba alineada con la columna del año y el texto se apilaba'
        );
    }

    public function test_encabezado_sin_agencia_queda_en_blanco_y_no_con_guion(): void
    {
        $tabla = $this->tablaGenerada('Agencia/Sede:');

        $this->assertStringContainsString('Agencia/Sede:', $tabla);
        $this->assertStringNotContainsString('—', $tabla, 'El cliente prefiere la celda vacía a un guion de relleno');
    }

    public function test_informe_completo_no_deja_marcadores_de_plantilla(): void
    {
        $documentXml = $this->documentXml($this->evaluadoCompleto());

        $this->assertSame([], InformeWordXml::problemasEstructura($documentXml));

        $texto = InformeWordXml::textoTablaConcatenado($documentXml);
        foreach (['xxxxx', 'xxxxxx', 'xxxxxxx'] as $marcador) {
            $this->assertStringNotContainsString($marcador, $texto);
        }
    }

    private function anchoPrimeraCelda(string $filaXml): int
    {
        $celdas = InformeWordXml::celdasFila($filaXml);
        $this->assertNotEmpty($celdas);

        preg_match('/<w:tcW\b[^>]*w:w="(\d+)"/', $celdas[0], $coincidencia);

        return (int) ($coincidencia[1] ?? 0);
    }

    private function tablaGenerada(string $marcador, ?EvaluadoOrden $evaluado = null): string
    {
        $documentXml = $this->documentXml($evaluado ?? $this->evaluadoCompleto());
        $limites = InformeWordXml::limitesTablaPorMarcador($documentXml, $marcador);

        $this->assertNotNull($limites, "La plantilla debe incluir la tabla {$marcador}");

        return substr($documentXml, $limites[0], $limites[1] - $limites[0]);
    }

    private function documentXml(EvaluadoOrden $evaluado): string
    {
        $evaluado = $evaluado->fresh(['cuestionario', 'orden', 'documentos']);
        $path = InformeWordExport::generar($evaluado->orden, $evaluado);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path) === true, 'El .docx generado debe ser un ZIP legible');
        $documentXml = (string) $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($path);

        return $documentXml;
    }

    /** @param array<string, mixed> $sobrescribir */
    private function evaluadoCompleto(array $sobrescribir = [], int $seccion = 0): EvaluadoOrden
    {
        Storage::fake('local');

        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'nombre' => 'Andrea Alejandra',
            'apellidos' => 'Popa Cahuex',
            'dpi' => '3146060840901',
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
            'resultado' => 'aprobado',
            'fecha_realizada' => '2026-08-17',
            'motivo_hecho_evaluacion' => 'Analizar tatuajes del evaluado',
            'observaciones' => 'Analizar tatuajes del evaluado',
        ]);

        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 5,
            'total_secciones' => 5,
            'progreso_porcentaje' => 100,
            'completado' => true,
            'bloqueado' => false,
            'terminos_aceptados' => true,
            'terminos_aceptados_at' => now(),
        ]);

        $this->guardarDatosPersonales($cuestionario, $seccion === 1 ? $sobrescribir : []);
        $this->guardarFamiliar($cuestionario, $seccion === 2 ? $sobrescribir : []);
        $this->guardarAcademicoLaboral($cuestionario);
        $this->guardarEconomico($cuestionario);

        return $evaluado->fresh(['cuestionario', 'orden']);
    }

    /** @param array<string, mixed> $sobrescribir */
    private function guardarDatosPersonales(Cuestionario $cuestionario, array $sobrescribir): void
    {
        CuestionarioRespuesta::guardarRespuestas(
            $cuestionario->id,
            CuestionarioSecciones::slug(1, 'preempleo'),
            array_merge([
                'nombres_completos' => 'Andrea Alejandra',
                'apellidos_completos' => 'Popa Cahuex',
                'tipo_identificacion' => 'dpi',
                'dpi' => '3146060840901',
                'fecha_nacimiento' => '2004-01-19',
                'edad' => '22',
                'estado_civil' => 'soltero',
                'nacionalidad' => 'Guatemala',
                'departamento_nacimiento' => 'Quetzaltenango',
                'municipio_nacimiento' => 'Quetzaltenango',
                'direccion_residencia' => '13 calle 0-137 zona 6',
                'departamento' => 'Quetzaltenango',
                'municipio' => 'Salcajá',
                'telefono_personal' => '59508455',
                'telefono_alternativo' => '50545599',
                'email_personal' => 'popaalr27@gmail.com',
                'igss' => '1122334',
                'nit' => '114491577',
                'licencia_conducir' => 'no aplica',
            ], $sobrescribir)
        );
    }

    /** @param array<string, mixed> $sobrescribir */
    private function guardarFamiliar(Cuestionario $cuestionario, array $sobrescribir): void
    {
        $slug = CuestionarioSecciones::slug(2, 'preempleo');

        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, $slug, array_merge([
            'convive_con' => 'padres,pareja',
            'padre_nombre' => 'Mauricio López',
            'padre_vive' => 'si',
            'padre_edad' => '62',
            'padre_telefono' => '55511222',
            'padre_direccion' => '9a avenida 2-15 zona 4, Quetzaltenango',
            'padre_ocupacion' => 'Agricultor',
            'madre_nombre' => 'Rosa López',
            'madre_vive' => 'si',
            'madre_edad' => '58',
            'madre_telefono' => '55533444',
            'madre_direccion' => 'Cantón Chuisuc, Salcajá',
            'madre_ocupacion' => 'Comerciante',
            'vive_con_pareja' => 'si',
            'pareja_tipo_relacion' => 'casado',
            'pareja_nombre' => 'Laura Méndez',
            'pareja_edad' => '33',
            'pareja_telefono' => '55555666',
            'pareja_direccion' => '12 avenida 3-45 zona 1, Quetzaltenango',
            'pareja_ocupacion' => 'Contadora',
            'pareja_tiempo_relacion' => '8 años',
            'pareja_calidad_relacion' => 'buena',
            'tuvo_matrimonio_union_hijos' => 'si',
            'expareja_nombre' => 'Juan Pérez Coyoy',
            'expareja_tipo_relacion' => 'noviazgo',
            'expareja_tiempo_relacion' => '4 años',
            'expareja_hijos_comun' => 'no',
            'expareja_problemas_legales' => 'no',
            'tiene_hijos' => 'no',
            'tiene_hermanos' => 'no',
        ], $sobrescribir));
    }

    private function guardarAcademicoLaboral(Cuestionario $cuestionario): void
    {
        $slug = CuestionarioSecciones::slug(3, 'preempleo');

        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, $slug, [
            'ultimo_nivel_academico' => 'universitario',
            'estudia_actualmente' => 'si',
            'situacion_laboral_actual' => 'empleado',
            'empresa_actual' => 'Distribuidora La Central S.A.',
            'puesto_actual' => 'Auxiliar contable',
            'salario_actual' => '4500',
        ]);

        CuestionarioRespuesta::guardarTabla($cuestionario->id, $slug, 'formacion_academica', [
            ['nivel' => 'diversificado', 'estado' => 'completo', 'carrera' => 'Perito Contador', 'institucion' => 'Colegio Dr. Rodolfo Robles', 'anio' => '2020'],
            ['nivel' => 'universitario', 'estado' => 'en_curso', 'carrera' => 'Contaduría Pública y Auditoría', 'institucion' => 'Universidad de San Carlos', 'anio' => '2026'],
        ]);

        CuestionarioRespuesta::guardarTabla($cuestionario->id, $slug, 'estudios_actuales', [
            [
                'horario' => '18:30 a 21:30, martes y jueves',
                'que_estudia' => 'Contaduría Pública y Auditoría',
                'institucion' => 'Universidad de San Carlos',
            ],
        ]);
    }

    private function guardarEconomico(Cuestionario $cuestionario): void
    {
        $slug = CuestionarioSecciones::slug(4, 'preempleo');

        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, $slug, [
            'tipo_vivienda' => 'familiar',
            'personas_hogar' => '4',
            'personas_contribuyen_gastos' => '2',
            'dependientes_economicos' => '0',
            'econ_dependientes_detalle' => 'No tiene dependientes',
            'econ_posee_vehiculos' => 'si',
            'econ_detalle_vehiculos' => 'Motocicleta Honda 2020',
            'econ_posee_propiedades' => 'no',
            'econ_ingresos_adicionales_detalle' => 'No posee ingresos adicionales',
            'econ_gastos_mensuales_aprox' => '2500',
            'econ_pretension_salarial' => '4500',
            'tiene_deudas' => 'no',
            'econ_es_fiador' => 'no',
            'econ_problemas_bancarios' => 'no',
            'econ_demandas_deudas' => 'no',
            'econ_problemas_sat' => 'no',
        ]);
    }
}
