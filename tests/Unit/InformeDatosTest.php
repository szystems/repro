<?php

namespace Tests\Unit;

use App\Models\Cuestionario;
use App\Models\CuestionarioRespuesta;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\EvaluadorNota;
use App\Models\Orden;
use App\Support\InformacionComplementaria;
use App\Support\InformeDatos;
use App\Support\InformePreempleo;
use App\Support\InformeWordExport;
use App\Support\InformeWordNarrativas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

/** Sprint G5 — fuente única formulario + overrides REPRO → Word. */
class InformeDatosTest extends TestCase
{
    use RefreshDatabase;

    private Cuestionario $cuestionario;

    private EvaluadoOrden $evaluado;

    private Orden $orden;

    protected function setUp(): void
    {
        parent::setUp();

        $empresa = Empresa::factory()->create();
        $this->orden = Orden::factory()->create(['empresa_id' => $empresa->id]);
        $this->evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'nombre' => 'Carlos',
            'apellidos' => 'Original Apellido',
            'dpi' => '1234567891011',
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
        ]);

        $this->cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $this->evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 6,
            'total_secciones' => 6,
            'progreso_porcentaje' => 100,
            'completado' => true,
        ]);
    }

    public function test_tatuajes_se_compilan_desde_seccion_antecedentes(): void
    {
        CuestionarioRespuesta::guardarTabla($this->cuestionario->id, 'antecedentes', 'tatuajes', [
            [
                'ubicacion' => 'Brazo derecho',
                'tamano' => 'Mediano',
                'descripcion' => 'Águila',
                'tiempo' => '5 años',
                'visible_uniforme' => 'no',
                'significado' => 'Familia',
            ],
        ]);

        $tablas = InformePreempleo::compilarTablas($this->cuestionario);

        $this->assertSame('Brazo derecho', $tablas['tatuajes'][0]['ubicacion'] ?? null);
    }

    public function test_override_personal_alimenta_encabezado_word(): void
    {
        InformePreempleo::guardarDesdeRequest(
            $this->evaluado->id,
            [
                'personal' => [
                    ['pregunta' => 'Nombres completos', 'respuesta' => 'Nombre Editado G5'],
                    ['pregunta' => 'Apellidos completos', 'respuesta' => 'Apellido Editado G5'],
                    ['pregunta' => 'Número de identificación', 'respuesta' => '9999888777666'],
                ],
            ],
            [],
            null
        );

        $encabezado = InformeDatos::encabezado($this->orden, $this->evaluado->fresh(['cuestionario']));

        $this->assertSame('Nombre Editado G5', $encabezado['nombres']);
        $this->assertSame('Apellido Editado G5', $encabezado['apellidos']);
        $this->assertSame('9999 88877 7666', $encabezado['dpi']);
        $this->assertStringContainsString('Nombre Editado G5', $encabezado['nombre']);
    }

    public function test_complementaria_override_alimenta_narrativas(): void
    {
        InformePreempleo::guardarDesdeRequest(
            $this->evaluado->id,
            [
                'complementaria' => [
                    ['pregunta' => '¿Está de acuerdo con las condiciones laborales que la empresa le ofrece?', 'respuesta' => 'Respuesta override G5'],
                    ['pregunta' => 'Observaciones adicionales', 'respuesta' => 'Nota REPRO G5'],
                ],
            ],
            [],
            null
        );

        $narrativas = InformeWordNarrativas::compilar(
            $this->orden,
            $this->evaluado->fresh(['cuestionario']),
            'preempleo'
        );

        $respuestas = array_column($narrativas['informacion_complementaria'], 'respuesta');
        $this->assertContains('Respuesta override G5', $respuestas);
        $this->assertContains('Nota REPRO G5', $respuestas);
    }

    public function test_word_incluye_bloques_laboral_y_economico_del_evaluador(): void
    {
        foreach ([
            'word_laboral' => 'Narrativa laboral G5 única',
            'word_economico' => 'Narrativa económica G5 única',
            'word_salud' => 'Salud G5',
            'word_habitos' => 'Hábitos G5',
            'word_sustancias' => 'Sustancias G5',
            'word_judicial' => 'Judicial G5',
        ] as $campo => $contenido) {
            EvaluadorNota::guardarNota($this->evaluado->id, $campo, '', $contenido, null);
        }

        $path = InformeWordExport::generar(
            $this->orden->fresh(),
            $this->evaluado->fresh(['cuestionario', 'orden.empresa', 'sede'])
        );

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path) === true);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($path);

        $this->assertIsString($xml);
        $this->assertStringContainsString('Narrativa laboral G5 única', $xml);
        $this->assertStringContainsString('Narrativa económica G5 única', $xml);
    }

    public function test_para_evaluado_agrega_bloques_y_tatuajes(): void
    {
        CuestionarioRespuesta::guardarTabla($this->cuestionario->id, 'antecedentes', 'tatuajes', [
            ['ubicacion' => 'Pierna', 'descripcion' => 'Rosa'],
        ]);

        EvaluadorNota::guardarNota($this->evaluado->id, 'word_laboral', '', 'Texto laboral', null);

        $datos = InformeDatos::paraEvaluado($this->evaluado->fresh(['cuestionario', 'orden.empresa', 'sede']));

        $this->assertSame('Texto laboral', $datos['bloques_word']['word_laboral'] ?? null);
        $this->assertSame('Pierna', $datos['tatuajes'][0]['ubicacion'] ?? null);
        $this->assertArrayHasKey('encabezado', $datos);
        $this->assertArrayHasKey('tablas', $datos);
    }
}
