<?php

namespace Tests\Unit;

use App\Models\Cuestionario;
use App\Models\CuestionarioRespuesta;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Support\CuestionarioPrecarga;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CuestionarioPrecargaTest extends TestCase
{
    use RefreshDatabase;

    public function test_construye_snapshot_desde_evaluado_y_orden(): void
    {
        $empresa = Empresa::factory()->create(['nombre' => 'Empresa Demo S.A.']);
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);

        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'nombre' => 'María',
            'apellidos' => 'López Pérez',
            'dpi' => '1234567890101',
            'email' => 'maria@example.com',
            'telefono' => '55551234',
            'direccion' => 'Zona 10, Ciudad de Guatemala',
            'puesto_evaluar' => 'Analista',
            'sede_region_empresa' => 'Región Central',
            'tipo_documento' => 'dpi',
        ]);

        $snapshot = CuestionarioPrecarga::construirDesdeEvaluado($evaluado);

        $this->assertSame('María', $snapshot['nombres_completos']);
        $this->assertSame('López Pérez', $snapshot['apellidos_completos']);
        $this->assertSame('1234567890101', $snapshot['dpi']);
        $this->assertSame('Empresa Demo S.A.', $snapshot['empresa_solicitante']);
        $this->assertSame('Región Central', $snapshot['agencia_region']);
        $this->assertSame('Analista', $snapshot['puesto_evaluar']);
        $this->assertSame('maria@example.com', $snapshot['email_personal']);
        $this->assertSame('55551234', $snapshot['telefono_personal']);
    }

    public function test_asegurar_snapshot_no_sobrescribe_existente(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'nombre' => 'Actualizado',
        ]);
        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 1,
            'total_secciones' => 5,
            'datos_precarga_json' => ['nombres_completos' => 'Original Orden'],
        ]);

        $snapshot = CuestionarioPrecarga::asegurarSnapshot($cuestionario, $evaluado);

        $this->assertSame('Original Orden', $snapshot['nombres_completos']);
    }

    public function test_metadata_marca_campo_modificado(): void
    {
        $snapshot = ['nombres_completos' => 'Juan', 'email_personal' => 'juan@old.com'];

        $meta = CuestionarioPrecarga::metadataTrazabilidad('nombres_completos', 'Pedro', $snapshot);

        $this->assertTrue($meta['precarga']['modificado']);
        $this->assertSame('Juan', $meta['precarga']['valor_orden']);
        $this->assertNotNull($meta['precarga']['modificado_at']);

        $sinCambio = CuestionarioPrecarga::metadataTrazabilidad('email_personal', 'juan@old.com', $snapshot);
        $this->assertFalse($sinCambio['precarga']['modificado']);
    }

    public function test_guardar_respuestas_registra_trazabilidad(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'nombre' => 'Juan',
            'apellidos' => 'Pérez',
        ]);

        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 1,
            'total_secciones' => 5,
            'datos_precarga_json' => CuestionarioPrecarga::construirDesdeEvaluado($evaluado),
        ]);

        $snapshot = $cuestionario->datos_precarga_json;

        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'datos_personales', [
            'nombres_completos' => 'Carlos',
            'apellidos_completos' => 'Pérez',
        ], $snapshot);

        $respuesta = CuestionarioRespuesta::where('cuestionario_id', $cuestionario->id)
            ->where('campo', 'nombres_completos')
            ->first();

        $this->assertTrue($respuesta->metadata['precarga']['modificado']);
        $this->assertSame('Juan', $respuesta->metadata['precarga']['valor_orden']);

        $cambios = CuestionarioPrecarga::cambiosRegistrados($cuestionario->fresh('respuestas'));
        $this->assertCount(1, $cambios);
        $this->assertSame('nombres_completos', $cambios[0]['campo']);
    }
}
