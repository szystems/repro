<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultadosVisibilidadTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que admin/repro puede toggle la visibilidad de resultados
     */
    public function test_repro_can_toggle_resultados_visibles(): void
    {
        $repro = User::factory()->create(['role_as' => 2]);
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'resultados_visibles_empresa' => false,
        ]);

        // Activar visibilidad
        $response = $this->actingAs($repro)
            ->patch(route('ordenes.toggle-resultados-visibles', $orden));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertTrue($orden->fresh()->resultados_visibles_empresa);

        // Desactivar visibilidad
        $response = $this->actingAs($repro)
            ->patch(route('ordenes.toggle-resultados-visibles', $orden));

        $response->assertRedirect();
        $this->assertFalse($orden->fresh()->resultados_visibles_empresa);
    }

    /**
     * Test que admin puede toggle la visibilidad de resultados
     */
    public function test_admin_can_toggle_resultados_visibles(): void
    {
        $admin = User::factory()->create(['role_as' => 3]);
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'resultados_visibles_empresa' => false,
        ]);

        $response = $this->actingAs($admin)
            ->patch(route('ordenes.toggle-resultados-visibles', $orden));

        $response->assertRedirect();
        $this->assertTrue($orden->fresh()->resultados_visibles_empresa);
    }

    /**
     * Test que usuario empresa NO puede toggle la visibilidad
     */
    public function test_empresa_cannot_toggle_resultados_visibles(): void
    {
        $empresa = Empresa::factory()->create();
        $usuarioEmpresa = User::factory()->create([
            'role_as' => 1,
            'empresa_id' => $empresa->id,
        ]);
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'resultados_visibles_empresa' => false,
        ]);

        $response = $this->actingAs($usuarioEmpresa)
            ->patch(route('ordenes.toggle-resultados-visibles', $orden));

        // Debe seguir en false
        $this->assertFalse($orden->fresh()->resultados_visibles_empresa);
    }

    /**
     * Test que empresa ve mensaje de "en proceso" cuando resultados no visibles
     */
    public function test_empresa_sees_in_process_message_when_results_not_visible(): void
    {
        $empresa = Empresa::factory()->create();
        $usuarioEmpresa = User::factory()->create([
            'role_as' => 1,
            'empresa_id' => $empresa->id,
        ]);
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'resultados_visibles_empresa' => false,
        ]);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'cuestionario_completado' => true,
        ]);

        $response = $this->actingAs($usuarioEmpresa)
            ->get(route('empresa.cuestionarios.show', $evaluado));

        $response->assertStatus(200);
        $response->assertSee('Resultados en Proceso');
        $response->assertSee('resultados estarán disponibles cuando la orden sea entregada');
    }

    /**
     * Test que empresa ve resultados cuando están habilitados
     */
    public function test_empresa_sees_results_when_visible(): void
    {
        $empresa = Empresa::factory()->create();
        $usuarioEmpresa = User::factory()->create([
            'role_as' => 1,
            'empresa_id' => $empresa->id,
        ]);
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'resultados_visibles_empresa' => true,
            'estado' => 'entregado',
        ]);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'cuestionario_completado' => true,
        ]);

        $response = $this->actingAs($usuarioEmpresa)
            ->get(route('empresa.cuestionarios.show', $evaluado));

        $response->assertStatus(200);
        $response->assertSee('Cuestionario Completado');
        $response->assertDontSee('Resultados en Proceso de Validación');
    }

    /**
     * Test que el listado muestra badge "En proceso" cuando no visible para empresa
     */
    public function test_cuestionarios_list_shows_in_process_badge(): void
    {
        $empresa = Empresa::factory()->create();
        $usuarioEmpresa = User::factory()->create([
            'role_as' => 1,
            'empresa_id' => $empresa->id,
        ]);
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'resultados_visibles_empresa' => false,
        ]);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'cuestionario_completado' => true,
        ]);

        $response = $this->actingAs($usuarioEmpresa)
            ->get(route('empresa.cuestionarios'));

        $response->assertStatus(200);
        $response->assertSee('En proceso');
    }

    /**
     * BUG-02 (2026-04-22): el PDF de la orden contiene SOLO metadatos administrativos
     * (datos de la empresa, evaluados solicitados, fechas), no resultados sensibles.
     * Por lo tanto la empresa siempre puede descargarlo, aun sin resultados visibles.
     */
    public function test_empresa_can_generate_pdf_when_results_not_visible(): void
    {
        $empresa = Empresa::factory()->create();
        $usuarioEmpresa = User::factory()->create([
            'role_as' => 1,
            'empresa_id' => $empresa->id,
        ]);
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'resultados_visibles_empresa' => false,
        ]);

        $response = $this->actingAs($usuarioEmpresa)
            ->get(route('ordenes.pdf', $orden));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /**
     * Test que empresa SÍ puede generar PDF cuando resultados visibles
     */
    public function test_empresa_can_generate_pdf_when_results_visible(): void
    {
        $empresa = Empresa::factory()->create();
        $usuarioEmpresa = User::factory()->create([
            'role_as' => 1,
            'empresa_id' => $empresa->id,
        ]);
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'resultados_visibles_empresa' => true,
            'estado' => 'entregado',
        ]);

        $response = $this->actingAs($usuarioEmpresa)
            ->get(route('ordenes.pdf', $orden));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /**
     * Test que repro puede generar PDF sin importar visibilidad
     */
    public function test_repro_can_generate_pdf_regardless_of_visibility(): void
    {
        $repro = User::factory()->create(['role_as' => 2]);
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'resultados_visibles_empresa' => false,
        ]);

        $response = $this->actingAs($repro)
            ->get(route('ordenes.pdf', $orden));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /**
     * BUG-03 (2026-04-22): el listado de evaluaciones del cliente debe mostrar
     * TODOS los evaluados de sus órdenes (en cualquier estado), no solo los entregados
     * con visibilidad activa. La visibilidad solo restringe ver los resultados detallados.
     */
    public function test_evaluaciones_report_shows_all_empresa_evaluados(): void
    {
        $empresa = Empresa::factory()->create();
        $usuarioEmpresa = User::factory()->create([
            'role_as' => 1,
            'empresa_id' => $empresa->id,
        ]);

        $ordenVisible = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'resultados_visibles_empresa' => true,
            'estado' => 'entregado',
        ]);
        EvaluadoOrden::factory()->create([
            'orden_id' => $ordenVisible->id,
            'nombre' => 'Juan Visible',
        ]);

        $ordenEnProceso = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'resultados_visibles_empresa' => false,
        ]);
        EvaluadoOrden::factory()->create([
            'orden_id' => $ordenEnProceso->id,
            'nombre' => 'Pedro EnProceso',
        ]);

        $response = $this->actingAs($usuarioEmpresa)
            ->get(route('reportes.evaluaciones'));

        $response->assertStatus(200);
        $response->assertSee('Juan Visible');
        $response->assertSee('Pedro EnProceso');
    }

    /**
     * (2026-04-27): el listado "Mis Reportes" debe mostrar el botón de descarga
     * de PDF del informe del evaluado cuando la orden tenga resultados visibles
     * para la empresa, y un indicador "En proceso" cuando aún no.
     */
    public function test_evaluaciones_report_shows_pdf_button_when_results_available(): void
    {
        $empresa = Empresa::factory()->create();
        $usuarioEmpresa = User::factory()->create([
            'role_as' => 1,
            'empresa_id' => $empresa->id,
        ]);

        $ordenDisponible = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'resultados_visibles_empresa' => true,
            'estado' => 'entregado',
        ]);
        $evaluadoDisponible = EvaluadoOrden::factory()->create([
            'orden_id' => $ordenDisponible->id,
            'nombre' => 'EvaluadoConInforme',
        ]);

        $ordenEnProceso = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'resultados_visibles_empresa' => false,
        ]);
        EvaluadoOrden::factory()->create([
            'orden_id' => $ordenEnProceso->id,
            'nombre' => 'EvaluadoSinInforme',
        ]);

        $response = $this->actingAs($usuarioEmpresa)
            ->get(route('reportes.evaluaciones'));

        $response->assertStatus(200);
        // Botón PDF debe estar presente para el evaluado con resultados disponibles
        $response->assertSee(route('empresa.cuestionarios.pdf', $evaluadoDisponible), false);
        // Indicador "En proceso" para el otro evaluado
        $response->assertSee('En proceso');
    }
}
