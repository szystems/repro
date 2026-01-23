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
     * Test que empresa NO puede generar PDF si resultados no visibles
     */
    public function test_empresa_cannot_generate_pdf_when_results_not_visible(): void
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

        $response->assertRedirect();
        $response->assertSessionHas('error');
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
     * Test que reporte evaluaciones filtra por visibilidad para empresa
     */
    public function test_evaluaciones_report_filters_by_visibility_for_empresa(): void
    {
        $empresa = Empresa::factory()->create();
        $usuarioEmpresa = User::factory()->create([
            'role_as' => 1,
            'empresa_id' => $empresa->id,
        ]);
        
        // Orden con resultados visibles Y estado entregado
        $ordenVisible = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'resultados_visibles_empresa' => true,
            'estado' => 'entregado',
        ]);
        $evaluadoVisible = EvaluadoOrden::factory()->create([
            'orden_id' => $ordenVisible->id,
            'nombre' => 'Juan Visible',
        ]);
        
        // Orden con resultados NO visibles
        $ordenOculta = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'resultados_visibles_empresa' => false,
        ]);
        $evaluadoOculto = EvaluadoOrden::factory()->create([
            'orden_id' => $ordenOculta->id,
            'nombre' => 'Pedro Oculto',
        ]);

        $response = $this->actingAs($usuarioEmpresa)
            ->get(route('reportes.evaluaciones'));

        $response->assertStatus(200);
        $response->assertSee('Juan Visible');
        $response->assertDontSee('Pedro Oculto');
    }
}
