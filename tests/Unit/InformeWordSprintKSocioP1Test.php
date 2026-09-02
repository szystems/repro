<?php

namespace Tests\Unit;

use App\Models\Cuestionario;
use App\Models\CuestionarioRespuesta;
use App\Models\EvaluadorNota;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Support\InformeWordExport;
use App\Support\InformeWordXml;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

/** P1 Word 21-ago: expareja, domicilio, presupuesto Q, deudas, salud en fila extra. */
class InformeWordSprintKSocioP1Test extends TestCase
{
    use RefreshDatabase;

    public function test_socio_traslada_expareja_domicilio_presupuesto_deudas_y_salud(): void
    {
        $xml = $this->xmlSocioP1();
        $plano = $this->textoPlano($xml);

        $this->assertTrue(str_contains($plano, 'Nombre: Luis Expareja P1'), 'Falta expareja en texto');
        $this->assertTrue(str_contains($plano, 'Zona 9 P1, Quetzaltenango'), 'Falta domicilio de tabla 6');
        $this->assertTrue(str_contains($plano, '3 años P1'));
        $this->assertTrue(str_contains($plano, 'Alquilada'));
        $this->assertTrue(str_contains($plano, 'Q350'));
        $this->assertTrue(str_contains($plano, 'Q120'), 'Transporte no se trasladó');
        $this->assertTrue(str_contains($plano, 'Q80'), 'Servicios no se trasladó');
        $this->assertTrue(str_contains($plano, 'Q40'), 'Gastos médicos no se trasladó');
        $this->assertTrue(str_contains($plano, 'Q25'), 'Manutención no se trasladó');
        $this->assertTrue(str_contains($plano, 'Internet extra P1'));
        $this->assertTrue(str_contains($plano, 'Banco P1'));
        $this->assertTrue(str_contains($plano, 'Q5000') || str_contains($plano, 'Q5,000') || str_contains($plano, 'Q5000.00'));
        $this->assertTrue(str_contains($plano, 'Obs economica P1') || str_contains($plano, 'deudas'), 'Faltan observaciones económicas');
        $this->assertTrue(str_contains($plano, 'Estado general de salud'));
        $this->assertTrue(str_contains($plano, 'Salud fila extra P1'));

        $limSalud = InformeWordXml::limitesTablaPorMarcador($xml, 'ASPECTOS DE SALUD');
        $this->assertNotNull($limSalud);
        $tablaSalud = substr($xml, $limSalud[0], $limSalud[1] - $limSalud[0]);
        $this->assertTrue(str_contains($tablaSalud, 'Observaciones:'));
        $this->assertTrue(str_contains($tablaSalud, 'Salud fila extra P1'));
        $this->assertTrue(str_contains($tablaSalud, 'Estado general de salud'));
        $ultima = InformeWordXml::filasTabla($tablaSalud);
        $ultima = $ultima[array_key_last($ultima)] ?? '';
        $this->assertStringContainsString('gridSpan', $ultima, 'M-S4: la narrativa de salud debe ir en fila combinada');
        $this->assertSame(1, count(InformeWordXml::celdasFila($ultima)));
    }

    public function test_poligrafo_sigue_llenando_expareja_por_marcador_plural(): void
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
        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'informacion_familiar', [
            'tuvo_matrimonio_union_hijos' => 'si',
            'expareja_nombre' => 'Ana Ex Poligrafo',
            'expareja_tipo_relacion' => 'union_libre',
            'expareja_tiempo_relacion' => '2 años',
            'expareja_hijos_comun' => 'no',
            'expareja_problemas_legales' => 'no',
        ]);

        $xml = $this->xmlDe($orden, $evaluado);
        $this->assertTrue(str_contains($this->textoPlano($xml), 'Ana Ex Poligrafo'));
    }

    private function xmlSocioP1(): string
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

        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'datos_personales', [
            'direccion_residencia' => 'Zona 9 P1',
            'municipio' => 'Quetzaltenango',
            'departamento' => 'Quetzaltenango',
        ]);
        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'informacion_familiar', [
            'tuvo_matrimonio_union_hijos' => 'si',
            'expareja_nombre' => 'Luis Expareja P1',
            'expareja_tipo_relacion' => 'union_libre',
            'expareja_tiempo_relacion' => '4 años',
            'expareja_hijos_comun' => 'si',
            'expareja_cantidad_hijos' => 1,
            'expareja_problemas_legales' => 'no',
        ]);
        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'situacion_economica', [
            'tiene_deudas' => 'si',
            'detalle_deudas' => 'Obs economica P1',
        ]);
        CuestionarioRespuesta::guardarTabla($cuestionario->id, 'situacion_economica', 'deudas', [[
            'entidad' => 'Banco P1',
            'monto' => '5000',
            'saldo' => '2000',
            'cuota' => '400',
            'motivo' => 'Vehículo',
            'antiguedad' => '1 año',
            'estatus' => 'al_dia',
            'meses_atraso' => '',
        ]]);
        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'informacion_socioeconomica_complementaria', [
            'viv_tiempo_residencia' => '3 años P1',
            'viv_tipo_vivienda' => 'alquilada',
            'viv_monto_alquiler' => '350',
            'viv_propietario' => 'Dueño P1',
            'viv_habitantes_detalle' => '2 personas P1',
            'viv_refs_ubicacion' => 'Frente al parque P1',
            'viv_zona_riesgo' => 'no',
            'viv_direcciones_anteriores' => 'Zona 1 anterior P1',
        ]);
        CuestionarioRespuesta::guardarTabla($cuestionario->id, 'informacion_socioeconomica_complementaria', 'presupuesto', [
            ['concepto' => 'Alimentación:', 'monto' => '10'],
            ['concepto' => 'Cuota de alquiler:', 'monto' => '9.98'],
            ['concepto' => 'Vestuario:', 'monto' => '10'],
            ['concepto' => 'Transporte: (gasolina o pasajes)', 'monto' => '120'],
            ['concepto' => 'Pago de servicios básico (agua, luz, cable, teléfono, internet)', 'monto' => '80'],
            ['concepto' => 'Gastos méditos:', 'monto' => '40'],
            ['concepto' => 'Colegiaturas:', 'monto' => '9.95'],
            ['concepto' => 'Cuota mensual de préstamos:', 'monto' => '10'],
            ['concepto' => 'Cuota de manutención:', 'monto' => '25'],
            ['concepto' => 'Otros gastos:', 'monto' => '10'],
            ['concepto' => 'Internet extra P1', 'monto' => '15'],
        ]);
        EvaluadorNota::guardarNota($evaluado->id, 'word_salud', '', 'Salud fila extra P1', null);

        return $this->xmlDe($orden, $evaluado);
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
