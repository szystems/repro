<?php

namespace Tests\Unit;

use App\Models\Cuestionario;
use App\Models\CuestionarioRespuesta;
use App\Models\EvaluadoOrden;
use App\Models\EvaluadorNota;
use App\Models\Orden;
use App\Support\AsistenteRedaccionAvisos;
use App\Support\AsistenteRedaccionSecciones;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AsistenteRedaccionSeccionesTest extends TestCase
{
    use RefreshDatabase;

    public function test_laboral_economico_y_drogas_no_llevan_identidad_ni_el_jefe(): void
    {
        $evaluado = $this->evaluado('poligrafo', 'preempleo');
        $evaluado->update(['nombre' => 'Cristopher Unico', 'dpi' => '3403384231403', 'resultado' => 'no_aprobado']);
        $cuestionario = $evaluado->cuestionario;

        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'historial_laboral', [
            'integridad_04' => 'Manejó caja.',
            'integridad_05' => '',
        ]);
        CuestionarioRespuesta::guardarTabla($cuestionario->id, 'historial_laboral', 'empleos', [[
            'empresa' => 'Panadería Luz',
            'puesto' => 'Cajero',
            'fechas_laboradas' => '2020-2022',
            'motivo_retiro' => 'Renuncia',
            'jefe_inmediato' => 'Pedro Jefe 55551234',
            'contacto_rrhh' => 'Ana RRHH 55559999',
        ]]);
        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'situacion_economica', [
            'tiene_deudas' => 'si',
            'econ_pretension_salarial' => '5000',
        ]);
        CuestionarioRespuesta::guardarTabla($cuestionario->id, 'situacion_economica', 'deudas', [[
            'entidad' => 'Banco Industrial',
            'monto' => '45000',
            'saldo' => '28000',
            'cuota' => '1150',
            'motivo' => 'Préstamo personal',
            'estatus' => 'al_dia',
        ]]);
        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'antecedentes', [
            'sustancias_usadas' => 'marihuana',
            'salud_alergias' => 'si',
            'dpi' => '3403384231403',
        ]);

        $evaluado = $evaluado->fresh();
        $laboral = json_encode(AsistenteRedaccionSecciones::paquete($evaluado, 'word_laboral', ''), JSON_UNESCAPED_UNICODE);
        $economico = json_encode(AsistenteRedaccionSecciones::paquete($evaluado, 'word_economico', ''), JSON_UNESCAPED_UNICODE);
        $drogas = json_encode(AsistenteRedaccionSecciones::paquete($evaluado, 'word_sustancias', ''), JSON_UNESCAPED_UNICODE);

        $this->assertIsString($laboral);
        $this->assertStringContainsString('Panadería Luz', $laboral);
        $this->assertStringContainsString('Manejó caja.', $laboral);
        $this->assertStringNotContainsString('Pedro Jefe', $laboral);
        $this->assertStringNotContainsString('55551234', $laboral);
        $this->assertStringNotContainsString('Cristopher Unico', $laboral);
        $this->assertStringNotContainsString('3403384231403', $laboral);
        $this->assertStringNotContainsString('no_aprobado', $laboral);

        $this->assertStringContainsString('Banco Industrial', $economico);
        $this->assertStringContainsString('Al día', $economico);
        $this->assertStringContainsString('5000', $economico);
        $this->assertStringNotContainsString('Cristopher Unico', $economico);

        $this->assertStringContainsString('Marihuana', $drogas);
        $this->assertStringNotContainsString('salud_alergias', $drogas);
        $this->assertStringNotContainsString('3403384231403', $drogas);
    }

    public function test_un_vacio_no_se_vuelve_no_y_la_contradiccion_solo_avisa(): void
    {
        $evaluado = $this->evaluado('vsa', 'preempleo');
        CuestionarioRespuesta::guardarRespuestas($evaluado->cuestionario->id, 'antecedentes', [
            'salud_alergias' => '',
            'salud_estado_general' => 'buena',
        ]);

        $paquete = AsistenteRedaccionSecciones::paquete(
            $evaluado->fresh(),
            'word_salud',
            'Durante la entrevista refiere alergia a la penicilina.'
        );
        $avisos = AsistenteRedaccionAvisos::de($paquete, true);
        $alergias = collect($paquete['campos'])->firstWhere('clave', 'salud_alergias');

        $this->assertTrue($alergias['vacio']);
        $this->assertSame('', $alergias['texto']);
        $this->assertTrue(collect($avisos)->contains(fn (string $aviso): bool => str_contains($aviso, 'no es un no')));
        $this->assertTrue(collect($avisos)->contains(fn (string $aviso): bool => str_contains($aviso, 'alerg')));
        $this->assertSame('', $evaluado->fresh()->cuestionario->obtenerRespuestasSeccion(5)['salud_alergias'] ?? '');
    }

    public function test_recomendaciones_no_llevan_el_resultado(): void
    {
        $evaluado = $this->evaluado('socioeconomico', 'preempleo');
        $evaluado->update(['resultado' => 'no_aprobado', 'texto_informe_preliminar' => 'Se recomienda no contratar.']);
        EvaluadorNota::guardarNota($evaluado->id, 'word_salud', '', 'Refiere buena salud.', null);

        $json = json_encode(
            AsistenteRedaccionSecciones::paquete($evaluado->fresh(), 'word_recomendaciones', 'Redactar con cautela.'),
            JSON_UNESCAPED_UNICODE
        );

        $this->assertIsString($json);
        $this->assertStringContainsString('Refiere buena salud.', $json);
        $this->assertStringNotContainsString('no_aprobado', $json);
        $this->assertStringNotContainsString('no contratar', $json);
    }

    private function evaluado(string $servicio, string $formulario): EvaluadoOrden
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => $servicio,
            'tipo_formulario' => $formulario,
            'nombre' => 'Ana',
            'apellidos' => 'Prueba',
        ]);
        Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => $formulario,
            'seccion_actual' => 5,
            'total_secciones' => 5,
            'progreso_porcentaje' => 100,
            'completado' => true,
            'bloqueado' => false,
            'terminos_aceptados' => true,
            'terminos_aceptados_at' => now(),
        ]);

        return $evaluado->fresh('cuestionario');
    }
}
