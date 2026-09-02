<?php

namespace Tests\Unit;

use App\Models\Cuestionario;
use App\Models\CuestionarioRespuesta;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Support\InformeWordExport;
use App\Support\InformeWordXml;
use App\Support\InformeWordZip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpWord\Settings;
use Tests\TestCase;
use ZipArchive;

/** Sprint F1 — Word no debe quedar en blanco tras editar datos del evaluado/cuestionario. */
class InformeWordSprintF1RellenoTest extends TestCase
{
    use RefreshDatabase;

    public function test_establecer_texto_celda_limpia_runs_multiples(): void
    {
        $celda = '<w:tc><w:tcPr><w:tcW w:w="2000" w:type="dxa"/></w:tcPr>'
            . '<w:p><w:r><w:t>xx</w:t></w:r><w:r><w:t>xx</w:t></w:r></w:p></w:tc>';

        $nueva = InformeWordXml::establecerTextoCelda($celda, 'Carlos Demo');

        $this->assertStringContainsString('Carlos Demo', $nueva);
        $this->assertStringContainsString('<w:tcPr>', $nueva);
        preg_match_all('/<w:t(?:\s|>)/', $nueva, $matches);
        $this->assertCount(1, $matches[0]);
        $this->assertSame('Carlos Demo', InformeWordXml::textoCelda($nueva));
    }

    public function test_word_preempleo_incluye_encabezado_y_datos_familiares_editados(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'nombre' => 'Jenifer',
            'apellidos' => 'Mejia Prueba',
            'dpi' => '1234567891011',
            'puesto_evaluar' => 'Auxiliar',
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
            'telefono' => '50255551212',
        ]);

        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 6,
            'total_secciones' => 6,
            'progreso_porcentaje' => 100,
            'completado' => true,
        ]);

        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'informacion_familiar', [
            'padre_nombre' => 'Padre Editado Word',
            'padre_vive' => 'si',
            'padre_edad' => '55',
            'madre_nombre' => 'Madre Editada Word',
            'madre_vive' => 'si',
            'madre_edad' => '52',
        ]);

        \App\Support\InformePreempleo::guardarDesdeRequest(
            $evaluado->id,
            [
                'personal' => [
                    ['pregunta' => 'Nombres completos', 'respuesta' => 'Jenifer Editada'],
                    ['pregunta' => 'Apellidos completos', 'respuesta' => 'Apellido Editado Word'],
                ],
                'estudios_actuales' => [[
                    'horario' => 'Nocturno G5',
                    'que_estudia' => 'Administración',
                    'institucion' => 'Universidad G5',
                ]],
            ],
            [],
            null
        );

        $path = InformeWordExport::generar(
            $orden->fresh(),
            $evaluado->fresh(['cuestionario', 'orden.empresa', 'sede'])
        );

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path) === true);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($path);

        $this->assertIsString($xml);
        // Plantilla v2 separa Nombres / Apellidos en celdas distintas.
        $this->assertStringContainsString('Jenifer Editada', $xml);
        $this->assertStringContainsString('Apellido Editado Word', $xml);
        $this->assertStringContainsString('Padre Editado Word', $xml);
        $this->assertStringContainsString('Madre Editada Word', $xml);
        $this->assertStringContainsString('Administración', $xml);
        $this->assertStringContainsString('Nocturno G5', $xml);
    }

    public function test_word_preempleo_persiste_relleno_con_pclzip(): void
    {
        Settings::setZipClass(Settings::PCLZIP);

        $ref = new \ReflectionClass(InformeWordZip::class);
        $booted = $ref->getProperty('booted');
        $booted->setAccessible(true);
        $booted->setValue(null, false);

        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'nombre' => 'Aldin',
            'apellidos' => 'Prueba PCLZip',
            'dpi' => '1234567891011',
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
        ]);

        $path = InformeWordExport::generar($orden->fresh(), $evaluado->fresh(['cuestionario', 'orden.empresa', 'sede']));

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path) === true);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($path);

        $this->assertIsString($xml);
        $this->assertStringContainsString('Aldin', $xml);
        $this->assertStringContainsString('Prueba PCLZip', $xml);

        if (class_exists(\ZipArchive::class)) {
            Settings::setZipClass(Settings::ZIPARCHIVE);
            $booted->setValue(null, false);
        }
    }
}
