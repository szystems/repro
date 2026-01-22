<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear los roles necesarios para los tests
        Role::create(['name' => 'admin', 'display_name' => 'Administrador', 'description' => 'Admin']);
        Role::create(['name' => 'repro', 'display_name' => 'REPRO', 'description' => 'REPRO']);
        Role::create(['name' => 'empresa', 'display_name' => 'Empresa', 'description' => 'Empresa']);
    }

    /**
     * Helper para crear usuario admin con rol asignado
     */
    private function createAdminUser(): User
    {
        $user = User::factory()->create(['role_as' => 3, 'estado' => 1]);
        $user->roles()->attach(Role::where('name', 'admin')->first());
        return $user;
    }

    /**
     * Helper para crear usuario REPRO con rol asignado
     */
    private function createReproUser(): User
    {
        $user = User::factory()->create(['role_as' => 2, 'estado' => 1]);
        $user->roles()->attach(Role::where('name', 'repro')->first());
        return $user;
    }

    /**
     * Helper para crear usuario empresa con rol asignado
     */
    private function createEmpresaUser(Empresa $empresa): User
    {
        $user = User::factory()->create([
            'role_as' => 1,
            'estado' => 1,
            'empresa_id' => $empresa->id,
        ]);
        $user->roles()->attach(Role::where('name', 'empresa')->first());
        return $user;
    }

    /**
     * Test que la página de reportes de evaluaciones requiere autenticación.
     */
    public function test_reportes_evaluaciones_requires_authentication(): void
    {
        $response = $this->get(route('reportes.evaluaciones'));
        
        $response->assertRedirect(route('login'));
    }

    /**
     * Test que un usuario admin puede ver el reporte de evaluaciones.
     */
    public function test_admin_can_see_evaluaciones_report(): void
    {
        $admin = $this->createAdminUser();

        $empresa = Empresa::factory()->create(['estado' => 1]);
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'estado' => 'solicitud',
        ]);
        
        EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'estado_evaluacion' => 'completado',
        ]);

        $response = $this->actingAs($admin)->get(route('reportes.evaluaciones'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.reportes.evaluaciones');
        $response->assertViewHas('evaluados');
        $response->assertViewHas('stats');
        $response->assertViewHas('empresas');
        $response->assertSee('Reporte de Evaluaciones');
    }

    /**
     * Test que un usuario REPRO puede ver el reporte de evaluaciones.
     */
    public function test_repro_user_can_see_evaluaciones_report(): void
    {
        $repro = $this->createReproUser();

        $response = $this->actingAs($repro)->get(route('reportes.evaluaciones'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.reportes.evaluaciones');
    }

    /**
     * Test que un usuario de empresa puede ver el reporte de sus evaluaciones.
     */
    public function test_empresa_user_can_see_their_evaluaciones_report(): void
    {
        $empresa = Empresa::factory()->create(['estado' => 1]);
        $user = $this->createEmpresaUser($empresa);

        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'estado' => 'entregado',
        ]);

        EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'estado_evaluacion' => 'completado',
        ]);

        $response = $this->actingAs($user)->get(route('reportes.evaluaciones'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.reportes.evaluaciones');
    }

    /**
     * Test que un usuario de empresa NO puede ver el reporte de empresas.
     */
    public function test_empresa_user_cannot_see_empresas_report(): void
    {
        $empresa = Empresa::factory()->create(['estado' => 1]);
        $user = $this->createEmpresaUser($empresa);

        $response = $this->actingAs($user)->get(route('reportes.empresas'));

        $response->assertStatus(403);
    }

    /**
     * Test que un admin puede ver el reporte de empresas.
     */
    public function test_admin_can_see_empresas_report(): void
    {
        $admin = $this->createAdminUser();

        $empresa = Empresa::factory()->create(['estado' => 1]);
        Orden::factory()->count(3)->create([
            'empresa_id' => $empresa->id,
        ]);

        $response = $this->actingAs($admin)->get(route('reportes.empresas'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.reportes.empresas');
        $response->assertViewHas('empresas');
        $response->assertViewHas('stats');
        $response->assertSee('Reporte de Empresas');
    }

    /**
     * Test que el filtro por fecha funciona en evaluaciones.
     */
    public function test_evaluaciones_report_filters_by_date(): void
    {
        $admin = $this->createAdminUser();

        $empresa = Empresa::factory()->create(['estado' => 1]);
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'created_at' => now()->subDays(5),
        ]);

        EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'created_at' => now()->subDays(5),
        ]);

        // Buscar evaluados de los últimos 3 días (no debería encontrar el creado hace 5 días)
        $response = $this->actingAs($admin)->get(route('reportes.evaluaciones', [
            'fecha_inicio' => now()->subDays(3)->format('Y-m-d'),
            'fecha_fin' => now()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
        $evaluados = $response->viewData('evaluados');
        $this->assertEquals(0, $evaluados->count());
    }

    /**
     * Test que el filtro por empresa funciona en evaluaciones.
     */
    public function test_evaluaciones_report_filters_by_empresa(): void
    {
        $admin = $this->createAdminUser();

        $empresa1 = Empresa::factory()->create(['estado' => 1]);
        $empresa2 = Empresa::factory()->create(['estado' => 1]);

        $orden1 = Orden::factory()->create(['empresa_id' => $empresa1->id]);
        $orden2 = Orden::factory()->create(['empresa_id' => $empresa2->id]);

        EvaluadoOrden::factory()->create(['orden_id' => $orden1->id]);
        EvaluadoOrden::factory()->create(['orden_id' => $orden2->id]);

        // Filtrar solo por empresa 1
        $response = $this->actingAs($admin)->get(route('reportes.evaluaciones', [
            'empresa_id' => $empresa1->id,
        ]));

        $response->assertStatus(200);
        $evaluados = $response->viewData('evaluados');
        $this->assertEquals(1, $evaluados->count());
    }

    /**
     * Test de exportación PDF de evaluaciones.
     */
    public function test_admin_can_export_evaluaciones_pdf(): void
    {
        $admin = $this->createAdminUser();

        $empresa = Empresa::factory()->create(['estado' => 1]);
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);
        EvaluadoOrden::factory()->create(['orden_id' => $orden->id]);

        $response = $this->actingAs($admin)->get(route('reportes.evaluaciones.pdf'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /**
     * Test de exportación Excel de evaluaciones.
     */
    public function test_admin_can_export_evaluaciones_excel(): void
    {
        $admin = $this->createAdminUser();

        $empresa = Empresa::factory()->create(['estado' => 1]);
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);
        EvaluadoOrden::factory()->create(['orden_id' => $orden->id]);

        $response = $this->actingAs($admin)->get(route('reportes.evaluaciones.excel'));

        $response->assertStatus(200);
        $this->assertTrue(
            str_contains($response->headers->get('content-type'), 'spreadsheet') ||
            str_contains($response->headers->get('content-type'), 'excel') ||
            str_contains($response->headers->get('content-disposition'), '.xlsx')
        );
    }
}
