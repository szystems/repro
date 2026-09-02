<?php

namespace Tests\Unit;

use App\Models\Cuestionario;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\User;
use App\Support\InformePreempleo;
use App\Support\InformeWordExport;
use App\Support\InformeWordResultado;
use App\Support\InformeWordXml;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

/** Sprint N lote A — solo la opción en Word, sin complementaria laboral en UI, firmas de plantilla. */
class InformeWordSprintNLoteATest extends TestCase
{
    use RefreshDatabase;

    public function test_poli_aprobado_deja_solo_esa_fila_en_primera_y_ultima_hoja(): void
    {
        $xml = $this->xmlPoli(['resultado' => 'aprobado']);
        $primera = $this->tabla($xml, 'RESULTADO DE EVALUACIÓN');
        $ultima = $this->tabla($xml, 'NO HAY INDICACIÓN DE MENTIRA/ APROBADO');

        $this->assertStringContainsString('APROBADO', $primera);
        $this->assertStringNotContainsString('NO APROBADO', $primera);
        $this->assertStringNotContainsString('EXCEPCION', mb_strtoupper($this->textoPlano($primera)));
        $this->assertStringContainsString('APROBADO', $ultima);
        $this->assertStringNotContainsString('NO APROBADO', $ultima);
    }

    public function test_socio_tipo_c_deja_solo_esa_fila_en_clasificacion(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'socioeconomico',
            'tipo_formulario' => 'preempleo',
            'nombre' => 'Carmen',
            'apellidos' => 'LoteN',
            'resultado' => 'tipo_c',
        ]);
        Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'socioeconomico',
            'seccion_actual' => 6,
            'total_secciones' => 6,
            'completado' => true,
        ]);

        $xml = $this->xmlDe($orden, $evaluado);
        $primera = $this->textoPlano($this->tabla($xml, InformeWordResultado::MARCADOR_CLASIFICACION_SOCIO));
        $ultima = $this->textoPlano($this->tabla($xml, InformeWordResultado::MARCADOR_CONCLUSIONES_SOCIO));

        $this->assertStringContainsString('Tipo C', $primera);
        $this->assertStringContainsString('Tipo C', $ultima);
        $this->assertStringNotContainsString(InformeWordResultado::MARCA, $primera);
        $this->assertStringNotContainsString(InformeWordResultado::MARCA, $ultima);
        $this->assertStringNotContainsString('Tipo B', $primera);
        $this->assertStringNotContainsString('Condicionado', $primera);
    }

    public function test_tablas_informe_ya_no_listan_complementaria_laboral(): void
    {
        foreach (['preempleo', 'socioeconomico', 'periodica', 'especifica'] as $tipo) {
            $this->assertArrayNotHasKey(
                'labor_complementaria',
                InformePreempleo::clavesTablas($tipo),
                $tipo
            );
        }
        $this->assertArrayHasKey('complementaria', InformePreempleo::clavesTablas('preempleo'));
        $this->assertArrayHasKey('complementaria', InformePreempleo::clavesTablas('socioeconomico'));
        $this->assertArrayNotHasKey('complementaria', InformePreempleo::clavesTablas('periodica'));
        $this->assertArrayNotHasKey('complementaria', InformePreempleo::clavesTablas('especifica'));
        $this->assertArrayHasKey('laboral', InformePreempleo::clavesTablas('periodica'));
        $this->assertFalse(InformePreempleo::incluyeHermanos('periodica'));
        $this->assertFalse(InformePreempleo::incluyeHermanos('especifica'));
        $this->assertTrue(InformePreempleo::incluyeHermanos('preempleo'));
        $this->assertTrue(InformePreempleo::incluyeHermanos('socioeconomico'));
    }

    public function test_vsa_conserva_aldin_y_no_pone_al_poligrafista_de_la_orden(): void
    {
        $orden = Orden::factory()->create();
        $examinador = User::factory()->create(['name' => 'Examinador SprintN']);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'vsa',
            'tipo_formulario' => 'preempleo',
            'nombre' => 'Getzer',
            'apellidos' => 'LoteN',
            'poligrafista_id' => $examinador->id,
        ]);
        Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 6,
            'total_secciones' => 6,
            'completado' => true,
        ]);

        $plano = $this->textoPlano($this->xmlDe($orden, $evaluado));
        $this->assertStringContainsString('Aldin Tobar', $plano);
        $this->assertStringContainsString('Certified Examiner VSA', $plano);
        $this->assertStringNotContainsString('Examinador SprintN Certified Examiner VSA', $plano);
    }

    /** @param array<string, mixed> $atributos */
    private function xmlPoli(array $atributos): string
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create(array_merge([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
            'nombre' => 'Ericka',
            'apellidos' => 'LoteN',
        ], $atributos));
        Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 6,
            'total_secciones' => 6,
            'completado' => true,
        ]);

        return $this->xmlDe($orden, $evaluado);
    }

    private function tabla(string $xml, string $marcador): string
    {
        $limites = InformeWordXml::limitesTablaPorMarcador($xml, $marcador);
        $this->assertNotNull($limites, "Falta la tabla {$marcador}");

        return substr($xml, $limites[0], $limites[1] - $limites[0]);
    }

    private function xmlDe(Orden $orden, EvaluadoOrden $evaluado): string
    {
        $path = InformeWordExport::generar($orden->fresh(), $evaluado->fresh(['cuestionario', 'orden.empresa', 'sede', 'poligrafista', 'responsable']));
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path) === true);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($path);
        $this->assertIsString($xml);

        return $xml;
    }

    private function textoPlano(string $xml): string
    {
        return html_entity_decode(preg_replace('/<[^>]+>/', '', $xml) ?? '', ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
