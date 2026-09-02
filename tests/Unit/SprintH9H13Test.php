<?php

namespace Tests\Unit;

use App\Models\Cuestionario;
use App\Models\CuestionarioRespuesta;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Support\InformeWordExport;
use App\Support\InformeWordXml;
use App\Support\ResumenFamiliar;
use App\Support\TablaDinamica;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

class SprintH9H13Test extends TestCase
{
    use RefreshDatabase;

    public function test_resumen_familiar_detecta_pareja_y_expareja_con_datos(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create(['orden_id' => $orden->id]);
        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 2,
            'total_secciones' => 6,
        ]);

        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'informacion_familiar', [
            'vive_con_pareja' => 'no',
            'pareja_nombre' => 'María López',
            'pareja_edad' => '30',
            'tuvo_matrimonio_union_hijos' => 'no',
            'expareja_nombre' => 'Ex Cónyuge',
            'expareja_tipo_relacion' => 'matrimonio',
        ]);

        $familiar = ResumenFamiliar::compilar($cuestionario->fresh());

        $this->assertTrue($familiar['pareja']['tiene'] ?? false);
        $this->assertSame('María López', $familiar['pareja']['nombre']);
        $this->assertTrue($familiar['expareja']['aplica'] ?? false);
        $this->assertSame('Ex Cónyuge', $familiar['expareja']['nombre']);
    }

    public function test_validacion_currency_acepta_signo_q(): void
    {
        $reglas = TablaDinamica::reglasValidacion(4, 'preempleo');
        $this->assertContains('regex:/^[Qq]?\s*[\d.,]+$/', $reglas['deudas.*.monto']);
    }

    public function test_word_incluye_expareja_tatuajes_y_salario_con_q(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'nombre' => 'Edgar',
            'apellidos' => 'Escobar',
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
        ]);

        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 6,
            'total_secciones' => 6,
            'completado' => true,
        ]);

        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'informacion_familiar', [
            'vive_con_pareja' => 'si',
            'pareja_nombre' => 'Esposa Actual',
            'pareja_edad' => '28',
            'expareja_nombre' => 'Ex Esposa Test',
            'expareja_tipo_relacion' => 'matrimonio',
            'tuvo_matrimonio_union_hijos' => 'si',
        ]);

        CuestionarioRespuesta::guardarTabla($cuestionario->id, 'antecedentes', 'tatuajes', [[
            'ubicacion' => 'Brazo derecho',
            'tamano' => 'Mediano',
            'descripcion' => 'Dragón',
            'tiempo' => '5 años',
            'visible_uniforme' => 'no',
            'significado' => 'Arte',
        ]]);

        CuestionarioRespuesta::guardarTabla($cuestionario->id, 'historial_laboral', 'empleos', [[
            'empresa' => 'Empresa Q',
            'puesto' => 'Operador',
            'fechas_laboradas' => '01/2020 - 12/2022',
            'ultimo_salario' => 'Q4500',
            'motivo_retiro' => 'Renuncia',
        ]]);

        $path = InformeWordExport::generar($orden, $evaluado->fresh(['cuestionario', 'orden.empresa', 'sede']));
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path) === true);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($path);

        $this->assertIsString($xml);
        $this->assertStringContainsString('Ex Esposa Test', $xml);
        $this->assertStringContainsString('Esposa Actual', $xml);
        $this->assertStringContainsString('Brazo derecho', $xml);
        $this->assertStringContainsString('Q4500', $xml);
        $this->assertStringContainsString('01/2020 al 12/2022', $xml);
        $this->assertStringNotContainsString('xxxxxxx', $xml);
    }
}
