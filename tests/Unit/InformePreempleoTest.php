<?php

namespace Tests\Unit;

use App\Models\Cuestionario;
use App\Models\CuestionarioRespuesta;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Support\CamposInternosPreempleo;
use App\Support\InformacionComplementaria;
use App\Support\InformePreempleo;
use App\Support\ResumenFamiliar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InformePreempleoTest extends TestCase
{
    use RefreshDatabase;

    private Cuestionario $cuestionario;

    protected function setUp(): void
    {
        parent::setUp();

        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_formulario' => 'preempleo',
        ]);

        $this->cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 5,
            'total_secciones' => 6,
            'progreso_porcentaje' => 80,
            'estado' => 'en_progreso',
            'completado' => false,
        ]);

        CuestionarioRespuesta::guardarTabla($this->cuestionario->id, 'historial_laboral', 'formacion_academica', [
            [
                'nivel' => 'universitario',
                'estado' => 'completo',
                'carrera' => 'Derecho',
                'institucion' => 'USAC',
                'anio' => '2010',
                'respaldo' => 'si',
            ],
        ]);

        CuestionarioRespuesta::guardarTabla($this->cuestionario->id, 'historial_laboral', 'empleos', [
            [
                'empresa' => 'Empresa Demo SA',
                'puesto' => 'Analista',
                'fecha_ingreso' => '2020-01-01',
                'fecha_salida' => '',
                'ultimo_salario' => '5000',
                'motivo_retiro' => 'Actual',
                'jefe_inmediato' => 'Juan Pérez',
                'contacto_rrhh' => '5555-0000',
                'tiene_constancia' => 'si',
            ],
        ]);

        CuestionarioRespuesta::guardarTabla($this->cuestionario->id, 'situacion_economica', 'deudas', [
            [
                'entidad' => 'Banco Industrial',
                'monto' => '50000',
                'saldo' => '20000',
                'cuota' => '1500',
                'motivo' => 'Vivienda',
                'antiguedad' => '3 años',
                'estatus' => 'al_dia',
                'meses_atraso' => '0',
            ],
        ]);

        foreach (InformacionComplementaria::PREGUNTAS as $pregunta) {
            CuestionarioRespuesta::guardarRespuestas($this->cuestionario->id, 'antecedentes', [
                $pregunta['key'] => 'Respuesta demo '.$pregunta['key'],
            ]);
        }

        CuestionarioRespuesta::guardarRespuestas($this->cuestionario->id, 'informacion_familiar', [
            'padre_nombre' => 'Padre Demo',
            'padre_vive' => 'si',
            'convive_con' => 'padres',
        ]);
    }

    public function test_compila_tablas_desde_cuestionario(): void
    {
        $tablas = InformePreempleo::compilarTablas($this->cuestionario);

        $this->assertSame('USAC', $tablas['academico'][0]['institucion'] ?? null);
        $this->assertSame('Empresa Demo SA', $tablas['laboral'][0]['empresa'] ?? null);
        $this->assertSame('Banco Industrial', $tablas['deudas'][0]['entidad'] ?? null);
        $this->assertCount(count(InformacionComplementaria::PREGUNTAS), $tablas['complementaria']);
        $this->assertSame('Padre Demo', $tablas['familiar']['padre']['nombre'] ?? null);
    }

    public function test_override_del_evaluador_reemplaza_datos_compilados(): void
    {
        InformePreempleo::guardarDesdeRequest(
            $this->cuestionario->evaluado_orden_id,
            [
                'academico' => [[
                    'nivel' => 'universitario',
                    'estado' => 'completo',
                    'carrera' => 'Ingeniería editada',
                    'institucion' => 'Universidad Editada',
                    'anio' => '2012',
                    'respaldo' => 'si',
                ]],
            ],
            [],
            null
        );

        $tablas = InformePreempleo::tablasParaAdmin($this->cuestionario);

        $this->assertSame('Universidad Editada', $tablas['academico'][0]['institucion'] ?? null);
        $this->assertSame('USAC', InformePreempleo::compilarTablas($this->cuestionario)['academico'][0]['institucion'] ?? null);
        $this->assertContains('academico', InformePreempleo::clavesConOverride($this->cuestionario->evaluado_orden_id));
    }

    public function test_restaurar_elimina_override(): void
    {
        InformePreempleo::guardarDesdeRequest(
            $this->cuestionario->evaluado_orden_id,
            ['laboral' => [['empresa' => 'Override SA', 'puesto' => 'X', 'fecha_ingreso' => '2019-01-01', 'motivo_retiro' => 'X', 'tiene_constancia' => 'no']]],
            [],
            null
        );

        InformePreempleo::guardarDesdeRequest(
            $this->cuestionario->evaluado_orden_id,
            [],
            ['laboral' => '1'],
            null
        );

        $this->assertDatabaseMissing('evaluador_notas', [
            'evaluado_orden_id' => $this->cuestionario->evaluado_orden_id,
            'seccion' => InformePreempleo::SECCION_NOTAS,
            'campo' => 'laboral',
        ]);

        $tablas = InformePreempleo::tablasParaAdmin($this->cuestionario);
        $this->assertSame('Empresa Demo SA', $tablas['laboral'][0]['empresa'] ?? null);
    }

    public function test_filtrar_respuestas_para_empresa_excluye_internas(): void
    {
        $respuestas = [
            'nombre' => 'Carlos',
            'integridad_01' => 'no',
            'salud_estado_general' => 'bueno',
            'econ_tipo_vivienda' => 'propia',
            'comp_sindicato' => 'No',
        ];

        $filtradas = CamposInternosPreempleo::filtrarRespuestasParaEmpresa($respuestas, 'preempleo');

        $this->assertArrayHasKey('nombre', $filtradas);
        $this->assertArrayHasKey('comp_sindicato', $filtradas);
        $this->assertArrayNotHasKey('integridad_01', $filtradas);
        $this->assertArrayNotHasKey('salud_estado_general', $filtradas);
        $this->assertArrayNotHasKey('econ_tipo_vivienda', $filtradas);
    }

    public function test_resumen_familiar_coincide_con_tabla_informe(): void
    {
        $compilado = InformePreempleo::compilarTablas($this->cuestionario)['familiar'];
        $resumen = ResumenFamiliar::compilar($this->cuestionario);

        $this->assertSame($resumen['padre']['nombre'] ?? null, $compilado['padre']['nombre'] ?? null);
    }

    public function test_socio_incluye_referencias_en_claves_y_compilacion(): void
    {
        $ordenId = EvaluadoOrden::query()->find($this->cuestionario->evaluado_orden_id)?->orden_id;
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $ordenId,
            'tipo_servicio' => 'socioeconomico',
            'tipo_formulario' => 'preempleo',
        ]);

        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'socioeconomico',
            'seccion_actual' => 6,
            'total_secciones' => 6,
            'progreso_porcentaje' => 100,
            'estado' => 'completado',
            'completado' => true,
        ]);

        CuestionarioRespuesta::guardarTabla($cuestionario->id, 'informacion_socioeconomica_complementaria', 'referencias_familiares', [
            ['nombre' => 'Ana Pérez', 'parentesco' => 'Madre', 'telefono' => '50211111111', 'direccion' => 'Zona 1'],
        ]);
        CuestionarioRespuesta::guardarTabla($cuestionario->id, 'informacion_socioeconomica_complementaria', 'referencias_personales', [
            ['nombre' => 'Carlos Ruiz', 'relacion' => 'Amigo', 'telefono' => '50222222222', 'anos_conocerlo' => '5'],
        ]);

        $claves = InformePreempleo::clavesTablas('socioeconomico');
        $this->assertArrayHasKey('referencias_familiares', $claves);
        $this->assertArrayHasKey('referencias_personales', $claves);

        $tablas = InformePreempleo::compilarTablas($cuestionario);
        $this->assertSame('Ana Pérez', $tablas['referencias_familiares'][0]['nombre'] ?? null);
        $this->assertSame('Carlos Ruiz', $tablas['referencias_personales'][0]['nombre'] ?? null);

        InformePreempleo::guardarDesdeRequest(
            $evaluado->id,
            [
                'referencias_familiares' => [[
                    'nombre' => 'Ana Editada',
                    'parentesco' => 'Madre',
                    'telefono' => '50211111111',
                    'direccion' => 'Zona 1',
                ]],
            ],
            [],
            null
        );

        $this->assertContains('referencias_familiares', InformePreempleo::clavesConOverride($evaluado->id));
        $this->assertSame('Ana Editada', InformePreempleo::tablasParaAdmin($cuestionario)['referencias_familiares'][0]['nombre'] ?? null);
    }
}
