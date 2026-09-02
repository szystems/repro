<?php

namespace Tests\Unit;

use App\Models\Cuestionario;
use App\Models\CuestionarioRespuesta;
use App\Models\EvaluadorNota;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\User;
use App\Support\InformeWordBloquesEvaluador;
use App\Support\InformeWordExport;
use App\Support\InformeWordXml;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

/** Sprint M — observaciones 26-ago (foto MP, estado civil título, nombre, firma, deudas, socio). */
class InformeWordSprintMTest extends TestCase
{
    use RefreshDatabase;

    public function test_estado_civil_va_en_el_titulo_de_la_tabla(): void
    {
        $xml = $this->xmlPoliPreempleo();
        $tabla = $this->tabla($xml, 'ESTADO CIVIL');
        $filas = InformeWordXml::filasTabla($tabla);
        $this->assertNotEmpty($filas);
        $titulo = InformeWordXml::textoFila($filas[0]);
        $this->assertStringContainsString('ESTADO CIVIL', $titulo);
        $this->assertStringContainsString('Casado(a)', $titulo);
    }

    public function test_conclusion_lleva_el_nombre_y_no_el_marcador(): void
    {
        $xml = $this->xmlPoliPreempleo();
        $plano = $this->textoPlano($xml);
        $this->assertStringContainsString('Ericka SprintM', $plano);
        $this->assertStringNotContainsString('NOMBRE DEL CANDIDATO', $plano);
        $this->assertStringNotContainsString('NOMBREDECANDIDATO', $plano);
    }

    public function test_firma_conserva_el_bloque_institucional_de_la_plantilla(): void
    {
        $xml = $this->xmlPoliPreempleo();
        $plano = $this->textoPlano($xml);
        $this->assertStringContainsString('Stefanie Castro', $plano);
        $this->assertStringContainsString('Rodrigo Castro', $plano);
        $this->assertStringContainsString('Narda', $plano);
        $this->assertStringNotContainsString('Examinador SprintM Poligrafista Certificado', $plano);
    }

    public function test_totales_de_deudas_suman_montos_con_coma(): void
    {
        $xml = $this->xmlVsaPeriodicaConDeudas();
        $tabla = $this->tabla($xml, 'ASPECTO ECONÓMICO');
        $plano = $this->textoPlano($tabla);
        $this->assertTrue(
            str_contains($plano, '47,200.00') || str_contains($plano, '47200'),
            'Los totales de deudas no se sumaron: '.$plano
        );
        $this->assertStringNotContainsString('TOTALES: Q. Q. Q.', preg_replace('/\s+/', ' ', $plano) ?? '');
    }

    public function test_recomendaciones_socio_van_a_generalidades(): void
    {
        $xml = $this->xmlSocio();
        $tabla = $this->tabla($xml, 'RECOMENDACIONES - OBSERVACIONES');
        $this->assertStringContainsString('Recomendación socio SprintM', $tabla);

        $filas = InformeWordXml::filasTabla($tabla);
        $this->assertNotEmpty($filas);
        $this->assertStringNotContainsString('Recomendación socio SprintM', InformeWordXml::textoFila($filas[1]));
    }

    public function test_no_vuelca_referencias_laborales_del_candidato(): void
    {
        $xml = $this->xmlSocio();
        $this->assertStringNotContainsString('Empresa Ref Laboral SprintM', $xml);
    }

    private function xmlPoliPreempleo(): string
    {
        $orden = Orden::factory()->create();
        $examinador = User::factory()->create(['name' => 'Examinador SprintM']);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
            'nombre' => 'Ericka',
            'apellidos' => 'SprintM',
            'poligrafista_id' => $examinador->id,
        ]);
        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 6,
            'total_secciones' => 6,
            'completado' => true,
        ]);
        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'datos_personales', [
            'estado_civil' => 'casado',
        ]);
        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'informacion_familiar', [
            'vive_con_pareja' => 'si',
            'pareja_nombre' => 'Jesus SprintM',
            'pareja_edad' => '43',
        ]);

        return $this->xmlDe($orden, $evaluado);
    }

    private function xmlVsaPeriodicaConDeudas(): string
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'vsa',
            'tipo_formulario' => 'periodica',
            'nombre' => 'Briyith',
            'apellidos' => 'SprintM',
        ]);
        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'periodica',
            'seccion_actual' => 5,
            'total_secciones' => 5,
            'completado' => true,
        ]);
        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'actualizacion_datos', [
            'estado_civil' => 'union_libre',
        ]);
        CuestionarioRespuesta::guardarTabla($cuestionario->id, 'situacion_economica', 'deudas', [
            ['entidad' => 'Banco Industrial', 'monto' => 'Q.40,000.00', 'saldo' => 'Q.23,000.00', 'cuota' => 'Q.1,500.00'],
            ['entidad' => 'Banco Industrial', 'monto' => 'Q.7,200.00', 'saldo' => 'Q.6,000.00', 'cuota' => 'Q.6,000.00'],
        ]);

        return $this->xmlDe($orden, $evaluado);
    }

    private function xmlSocio(): string
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'socioeconomico',
            'tipo_formulario' => 'preempleo',
            'nombre' => 'Carmen',
            'apellidos' => 'SprintM',
        ]);
        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'socioeconomico',
            'seccion_actual' => 6,
            'total_secciones' => 6,
            'completado' => true,
        ]);
        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'datos_personales', [
            'estado_civil' => 'casado',
        ]);
        CuestionarioRespuesta::guardarTabla($cuestionario->id, 'informacion_socioeconomica_complementaria', 'referencias_laborales', [[
            'empresa' => 'Empresa Ref Laboral SprintM',
            'contacto' => 'Ing. Perez SprintM',
            'telefono' => '55552222',
            'puesto' => 'Analista',
        ]]);
        EvaluadorNota::guardarNota(
            $evaluado->id,
            InformeWordBloquesEvaluador::NOTA_RECOMENDACIONES,
            '',
            'Recomendación socio SprintM',
            null
        );

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
