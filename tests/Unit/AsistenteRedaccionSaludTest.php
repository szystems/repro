<?php

namespace Tests\Unit;

use App\Models\Cuestionario;
use App\Models\CuestionarioRespuesta;
use App\Models\EvaluadoOrden;
use App\Models\EvaluadorNota;
use App\Models\Orden;
use App\Support\AsistenteRedaccionSalud;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AsistenteRedaccionSaludTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_vacio_sigue_vacio_y_no_se_vuelve_no(): void
    {
        $evaluado = $this->evaluadoConSalud([
            'salud_estado_general' => 'bueno',
            'salud_alergias' => '',
            'salud_tratamiento_medico' => 'no',
            'salud_detalle_tratamiento' => '',
        ], 'Durante la entrevista refiere alergia a la penicilina.');

        $paquete = AsistenteRedaccionSalud::paquete($evaluado);
        $porClave = collect($paquete['campos'])->keyBy('clave');

        $this->assertSame('Buena', $porClave['salud_estado_general']['texto']);
        $this->assertFalse($porClave['salud_estado_general']['vacio']);

        $this->assertTrue($porClave['salud_alergias']['vacio']);
        $this->assertSame('', $porClave['salud_alergias']['texto']);
        $this->assertTrue($porClave['salud_alergias']['incompleto']);
        $this->assertStringContainsString('¿Padece alergias?: (vacío)', $paquete['texto_para_modelo']);
        $this->assertStringNotContainsString('¿Padece alergias?: No', $paquete['texto_para_modelo']);

        $this->assertSame('No', $porClave['salud_tratamiento_medico']['texto']);
        $this->assertTrue($porClave['salud_detalle_tratamiento']['vacio']);
        $this->assertFalse($porClave['salud_detalle_tratamiento']['incompleto']);

        $this->assertSame(
            'Durante la entrevista refiere alergia a la penicilina.',
            $paquete['observacion_evaluador']
        );
        $this->assertStringContainsString('alergia a la penicilina', $paquete['texto_para_modelo']);
    }

    public function test_el_paquete_no_lleva_identidad_ni_otras_secciones(): void
    {
        $evaluado = $this->evaluadoConSalud([
            'salud_alergias' => 'si',
            'salud_detalle_alergias' => 'Penicilina',
            'habito_tiempo_libre' => 'Lectura',
            'dpi' => '3403384231403',
        ]);
        $evaluado->update([
            'nombre' => 'Cristopher Unico',
            'dpi' => '3403384231403',
        ]);

        $json = json_encode(AsistenteRedaccionSalud::paquete($evaluado->fresh()), JSON_UNESCAPED_UNICODE);

        $this->assertIsString($json);
        $this->assertStringNotContainsString('Cristopher Unico', $json);
        $this->assertStringNotContainsString('3403384231403', $json);
        $this->assertStringNotContainsString('habito_tiempo_libre', $json);
        $this->assertStringNotContainsString('Lectura', $json);
        $this->assertStringContainsString('Penicilina', $json);
    }

    public function test_la_periodica_lee_la_seccion_de_antecedentes_recientes(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'periodica',
        ]);
        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'periodica',
            'seccion_actual' => 5,
            'total_secciones' => 5,
            'progreso_porcentaje' => 100,
            'completado' => true,
            'bloqueado' => false,
            'terminos_aceptados' => true,
            'terminos_aceptados_at' => now(),
        ]);
        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'antecedentes_recientes', [
            'salud_alergias' => 'si',
            'salud_detalle_alergias' => 'Polvo',
        ]);

        $paquete = AsistenteRedaccionSalud::paquete($evaluado->fresh());
        $alergias = collect($paquete['campos'])->firstWhere('clave', 'salud_alergias');

        $this->assertSame('Sí', $alergias['texto']);
        $this->assertSame('periodica', $paquete['formulario']);
    }

    /**
     * @param  array<string, mixed>  $respuestas
     */
    private function evaluadoConSalud(array $respuestas, string $nota = ''): EvaluadoOrden
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
            'nombre' => 'Ana',
            'apellidos' => 'Prueba',
        ]);
        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 5,
            'total_secciones' => 5,
            'progreso_porcentaje' => 100,
            'completado' => true,
            'bloqueado' => false,
            'terminos_aceptados' => true,
            'terminos_aceptados_at' => now(),
        ]);
        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'antecedentes', $respuestas);
        if ($nota !== '') {
            EvaluadorNota::guardarNota($evaluado->id, 'word_salud', '', $nota, null);
        }

        return $evaluado->fresh();
    }
}
