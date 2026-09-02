<?php

namespace Tests\Unit;

use App\Models\Cuestionario;
use App\Models\CuestionarioRespuesta;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Support\InformeWordExport;
use App\Support\InformeWordXml;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

/** P0 Word 21-ago: CONFIDENCIAL duplicado, padres socio, complementaria sin aspectos generales. */
class InformeWordSprintKSocioP0Test extends TestCase
{
    use RefreshDatabase;

    public function test_quita_aviso_confidencial_del_cuerpo_y_deja_el_texto_corto(): void
    {
        $xml = '<w:document><w:body>'
            .'<w:p><w:r><w:t>Antes</w:t></w:r></w:p>'
            .'<mc:AlternateContent><mc:Choice Requires="wps"><w:drawing><w:txbxContent>'
            .'<w:p><w:r><w:t>Este documento contiene INFORMACIÓN CONFIDENCIAL. Reserva.</w:t></w:r></w:p>'
            .'</w:txbxContent></w:drawing></mc:Choice></mc:AlternateContent>'
            .'<w:p><w:r><w:t>Este documento contiene INFORMACIÓN CONFIDENCIAL. Se suplica reserva.</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Familia</w:t></w:r></w:p>'
            .'</w:body></w:document>';

        $limpio = InformeWordXml::quitarAvisoConfidencialDuplicadoDelCuerpo($xml);

        $this->assertFalse(str_contains($limpio, 'mc:AlternateContent'));
        $this->assertFalse(str_contains($limpio, 'INFORMACIÓN CONFIDENCIAL'));
        $this->assertTrue(str_contains($limpio, 'Familia'));
        $this->assertTrue(str_contains($limpio, 'Antes'));
    }

    public function test_socio_traslada_padres_y_complementaria_sin_aspectos_generales(): void
    {
        $xml = $this->xmlSocioGenerado();
        $plano = $this->textoPlano($xml);

        $this->assertTrue(str_contains($plano, 'Carlos Padre P0'), 'Falta el padre en socio');
        $this->assertTrue(str_contains($plano, 'Ana Madre P0'), 'Falta la madre en socio');
        $this->assertTrue(str_contains($plano, 'UsuarioRedesP0'));
        $this->assertTrue(str_contains($plano, 'MetasP0'));
        $this->assertTrue(str_contains($plano, 'SindicatoP0'));
        $this->assertTrue(str_contains($plano, 'No laboré antes P0'));
        $this->assertFalse(
            str_contains($plano, 'Colaboración y actitud durante el proceso'),
            'Aspectos generales no deben quedar en complementaria'
        );
        $this->assertFalse(
            str_contains($xml, 'INFORMACIÓN CONFIDENCIAL'),
            'El aviso confidencial no debe repetirse en el cuerpo; queda en el pie'
        );
    }

    public function test_poligrafo_sigue_llenando_padres_y_quita_aspectos_generales(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
        ]);
        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 5,
            'total_secciones' => 5,
            'completado' => true,
        ]);
        $this->guardarPadresYComplementaria($cuestionario->id, 'informacion_familiar', 'antecedentes');

        $xml = $this->xmlDe($orden, $evaluado);
        $plano = $this->textoPlano($xml);

        $this->assertTrue(str_contains($plano, 'Carlos Padre P0'));
        $this->assertTrue(str_contains($plano, 'Ana Madre P0'));
        $this->assertTrue(str_contains($plano, 'SindicatoP0'));
        $this->assertFalse(str_contains($plano, 'Colaboración y actitud durante el proceso'));
    }

    private function xmlSocioGenerado(): string
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'socioeconomico',
            'tipo_formulario' => 'preempleo',
        ]);
        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'socioeconomico',
            'seccion_actual' => 6,
            'total_secciones' => 6,
            'completado' => true,
        ]);
        $this->guardarPadresYComplementaria(
            $cuestionario->id,
            'informacion_familiar',
            'antecedentes'
        );
        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'informacion_socioeconomica_complementaria', [
            'comp_ha_laborado_empresa' => 'No laboré antes P0',
        ]);

        return $this->xmlDe($orden, $evaluado);
    }

    private function guardarPadresYComplementaria(int $cuestionarioId, string $seccionFamiliar, string $seccionAntecedentes): void
    {
        CuestionarioRespuesta::guardarRespuestas($cuestionarioId, $seccionFamiliar, [
            'padre_nombre' => 'Carlos Padre P0',
            'padre_vive' => 'si',
            'padre_edad' => '60',
            'padre_telefono' => '55551111',
            'padre_direccion' => 'Zona 1',
            'padre_ocupacion' => 'Comerciante',
            'madre_nombre' => 'Ana Madre P0',
            'madre_vive' => 'si',
            'madre_edad' => '58',
            'madre_telefono' => '55552222',
            'madre_direccion' => 'Zona 2',
            'madre_ocupacion' => 'Ama de casa',
        ]);
        CuestionarioRespuesta::guardarRespuestas($cuestionarioId, $seccionAntecedentes, [
            'comp_licencia_conducir' => 'B / 2028',
            'comp_sindicato' => 'SindicatoP0',
            'comp_familiar_empresa' => 'No',
            'comp_como_se_entero' => 'ReferidoP0',
            'comp_condiciones_laborales' => 'Si',
            'comp_metas' => 'MetasP0',
            'comp_cualidades_defectos' => 'Puntual',
            'comp_redes_usuario' => 'UsuarioRedesP0',
            'informacion_adicional_final' => 'NO DEBE SALIR EN COMPLEMENTARIA',
        ]);
    }

    private function xmlDe(Orden $orden, EvaluadoOrden $evaluado): string
    {
        $path = InformeWordExport::generar($orden->fresh(), $evaluado->fresh(['cuestionario', 'orden.empresa', 'sede']));
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
