<?php

namespace Tests\Unit;

use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Support\InformeWordExport;
use App\Support\InformeWordPlantillas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

/** Sprint F3 — matriz de 7 plantillas Word por servicio. */
class InformeWordPlantillasV2Test extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public static function matrizServicios(): array
    {
        return [
            'poligrafo_preempleo' => ['poligrafo', 'preempleo', 'informe-poligrafo-preempleo-v2.docx'],
            'poligrafo_periodica' => ['poligrafo', 'periodica', 'informe-poligrafo-periodica-v2.docx'],
            'poligrafo_especifica' => ['poligrafo', 'especifica', 'informe-poligrafo-especifica-v2.docx'],
            'vsa_preempleo' => ['vsa', 'preempleo', 'informe-vsa-preempleo-v2.docx'],
            'vsa_periodica' => ['vsa', 'periodica', 'informe-vsa-periodica-v2.docx'],
            'vsa_especifica' => ['vsa', 'especifica', 'informe-vsa-especifica-v2.docx'],
            'socio' => ['socioeconomico', 'preempleo', 'informe-socioeconomico-v2.docx'],
        ];
    }

    public function test_las_plantillas_v2_rotulan_igss(): void
    {
        foreach (glob(resource_path('templates/*-v2.docx')) ?: [] as $path) {
            $zip = new ZipArchive();
            $this->assertTrue($zip->open($path) === true, basename($path));
            $xml = $zip->getFromName('word/document.xml');
            $zip->close();
            $this->assertIsString($xml);
            $plano = html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8');
            $this->assertStringContainsString('IGSS:', $plano, basename($path));
            $this->assertStringNotContainsString('Iggs', $plano, basename($path));
        }
    }

    /** @dataProvider matrizServicios */
    public function test_resuelve_plantilla_v2_por_servicio(string $servicio, string $formulario, string $archivo): void
    {
        $this->assertFileExists(resource_path('templates/' . $archivo));

        $evaluado = EvaluadoOrden::factory()->make([
            'tipo_servicio' => $servicio,
            'tipo_formulario' => $formulario,
        ]);

        $config = InformeWordPlantillas::resolver($evaluado);

        $this->assertNotNull($config);
        $this->assertSame(InformeWordPlantillas::LAYOUT_V2, $config['layout']);
        $this->assertStringEndsWith($archivo, $config['path']);
    }

    public function test_genera_word_v2_con_datos_generales(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'nombre' => 'Carlos',
            'apellidos' => 'V2 Prueba',
            'dpi' => '2405617300105',
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
            'puesto_evaluar' => 'Analista',
        ]);

        $path = InformeWordExport::generar($orden, $evaluado);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path) === true);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($path);

        $this->assertIsString($xml);
        $plano = html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8');
        $this->assertStringContainsString('DATOS GENERALES', $xml);
        $this->assertStringContainsString('IGSS:', $plano);
        $this->assertStringNotContainsString('Iggs', $plano);
        $this->assertStringContainsString('Carlos', $xml);
        $this->assertStringContainsString('V2 Prueba', $xml);
        $this->assertStringContainsString('INFORME POLIGR', $xml);
    }
}
