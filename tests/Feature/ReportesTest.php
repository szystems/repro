<?php

namespace Tests\Feature;

use App\Exports\EvaluacionesExport;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Role;
use App\Models\Sede;
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
            'estado' => 'orden_recibida',
        ]);
        
        EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'estado_evaluacion' => 'informe_final_enviado',
        ]);

        $response = $this->actingAs($admin)->get(route('reportes.evaluaciones'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.reportes.evaluaciones');
        $response->assertViewHas('evaluados');
        $response->assertViewHas('stats');
        $response->assertViewHas('empresas');
        $response->assertSee('INFORMES DE EMPRESAS');
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
            'estado_evaluacion' => 'informe_final_enviado',
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
     * El mes y las fechas de Informes de empresas filtran el día de la prueba, no el alta del registro.
     */
    public function test_evaluaciones_report_filters_by_date(): void
    {
        $admin = $this->createAdminUser();

        $empresa = Empresa::factory()->create(['estado' => 1]);
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);

        EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'nombre' => 'Dentro',
            'apellidos' => 'Rango',
            'created_at' => now()->subDays(20),
            'fecha_programada' => now()->subDay()->setTime(9, 30),
        ]);
        EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'nombre' => 'Fuera',
            'apellidos' => 'Rango',
            'created_at' => now(),
            'fecha_programada' => now()->subDays(10)->setTime(8, 0),
        ]);
        EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'nombre' => 'SinFecha',
            'apellidos' => 'Prueba',
            'created_at' => now(),
            'fecha_programada' => null,
        ]);

        $response = $this->actingAs($admin)->get(route('reportes.evaluaciones', [
            'fecha_inicio' => now()->subDays(3)->format('Y-m-d'),
            'fecha_fin' => now()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
        $response->assertSee('Fecha de la prueba');
        $response->assertSee('Dentro');
        $response->assertSee(now()->subDay()->format('d/m/Y'));
        $response->assertDontSee('Fuera');
        $response->assertDontSee('SinFecha');
        $this->assertEquals(1, $response->viewData('evaluados')->total());
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

    public function test_evaluaciones_report_muestra_filtro_sede(): void
    {
        $admin = $this->createAdminUser();
        $sede = Sede::factory()->create(['nombre' => 'Xela Centro', 'estado' => 1]);

        $response = $this->actingAs($admin)->get(route('reportes.evaluaciones'));

        $response->assertOk();
        $response->assertSee('name="sede_id"', false);
        $response->assertSee('Todas las sedes', false);
        $response->assertSee('Xela Centro', false);
        $this->assertTrue($response->viewData('sedes')->contains('id', $sede->id));
    }

    public function test_evaluaciones_report_filters_by_sede(): void
    {
        $admin = $this->createAdminUser();
        $sede1 = Sede::factory()->create(['estado' => 1]);
        $sede2 = Sede::factory()->create(['estado' => 1]);
        $empresa = Empresa::factory()->create(['estado' => 1]);
        $orden1 = Orden::factory()->create(['empresa_id' => $empresa->id, 'sede_id' => $sede1->id]);
        $orden2 = Orden::factory()->create(['empresa_id' => $empresa->id, 'sede_id' => $sede2->id]);

        EvaluadoOrden::factory()->create(['orden_id' => $orden1->id, 'sede_id' => $sede1->id]);
        EvaluadoOrden::factory()->create(['orden_id' => $orden2->id, 'sede_id' => $sede2->id]);

        $response = $this->actingAs($admin)->get(route('reportes.evaluaciones', [
            'sede_id' => $sede1->id,
        ]));

        $response->assertOk();
        $this->assertEquals(1, $response->viewData('evaluados')->count());
        $response->assertSee((string) $sede1->id, false);
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
        $disposition = (string) $response->headers->get('content-disposition');
        $contentType = (string) $response->headers->get('content-type');
        $this->assertTrue(
            str_contains($contentType, 'spreadsheet') ||
            str_contains($contentType, 'excel') ||
            str_contains($disposition, '.xlsx') ||
            str_contains($disposition, '.xls')
        );
    }

    public function test_evaluaciones_excel_html_incluye_datos(): void
    {
        $empresa = Empresa::factory()->create(['nombre' => 'PRUEBA EXCEL STATS', 'estado' => 1]);
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'codigo_orden' => 'ORD-TEST-STATS',
        ]);
        $reclutador = User::factory()->create(['name' => 'Ana Reclutadora', 'role_as' => 1, 'estado' => 1]);
        $orden->update(['reclutador_id' => $reclutador->id]);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'nombre' => 'Carmen',
            'apellidos' => 'Castillo',
            'dpi' => '1234567890101',
            'sede_region_empresa' => 'Sucursal Centro',
        ]);

        $html = (new EvaluacionesExport(collect([$evaluado->fresh(['orden.empresa', 'orden.reclutador'])])))->toHtmlTable();

        $this->assertStringContainsString('ORD-TEST-STATS', $html);
        $this->assertStringContainsString('PRUEBA EXCEL STATS', $html);
        $this->assertStringContainsString('Ana Reclutadora', $html);
        $this->assertStringContainsString('Sucursal Centro', $html);
        $this->assertStringContainsString('Carmen', $html);
        $this->assertStringContainsString('Castillo', $html);
        $this->assertStringContainsString('Reclutador', $html);
        $this->assertStringContainsString('Sede/Región empresa (cliente)', $html);
        $this->assertStringContainsString('Resultado', $html);
    }

    public function test_evaluaciones_excel_usa_el_dia_de_la_prueba(): void
    {
        $empresa = Empresa::factory()->create(['estado' => 1]);
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);
        $encargado = User::factory()->create(['name' => 'Luis Encargado', 'role_as' => 2, 'estado' => 1]);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'nombre' => 'Marta',
            'puesto_evaluar' => 'Cajera',
            'resultado' => 'aprobado',
            'tipo_servicio' => 'poligrafo',
            'responsable_id' => $encargado->id,
            'fecha_programada' => '2026-04-15 14:30:00',
            'created_at' => '2026-01-02 08:00:00',
        ]);

        $html = (new EvaluacionesExport(collect([$evaluado->fresh(['orden.empresa', 'orden.reclutador', 'responsable'])])))->toHtmlTable();

        $this->assertStringContainsString('Fecha de la prueba', $html);
        $this->assertStringContainsString('Hora de la prueba', $html);
        $this->assertStringContainsString('Encargado', $html);
        $this->assertStringContainsString('15/04/2026', $html);
        $this->assertStringContainsString('14:30', $html);
        $this->assertStringContainsString('Luis Encargado', $html);
        $this->assertStringContainsString('Cajera', $html);
        $this->assertStringContainsString('02/01/2026', $html);
    }

    public function test_evaluaciones_excel_incluye_resultado_en_columna(): void
    {
        $empresa = Empresa::factory()->create(['estado' => 1]);
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'resultado' => 'aprobado',
            'tipo_servicio' => 'poligrafo',
        ]);
        $evaluado->setRelation('orden', $orden);

        $html = (new EvaluacionesExport(collect([$evaluado])))->toHtmlTable();

        $this->assertStringContainsString('>Resultado<', $html);
        $this->assertStringContainsString('Aprobado', $html);
        $this->assertStringNotContainsString('No liberado', $html);
    }

    public function test_evaluaciones_excel_oculta_resultado_no_liberado_a_la_empresa(): void
    {
        $empresa = Empresa::factory()->create(['estado' => 1]);
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'resultados_visibles_empresa' => false,
            'estado' => 'en_proceso',
        ]);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'resultado' => 'no_aprobado',
            'tipo_servicio' => 'poligrafo',
        ]);

        $html = (new EvaluacionesExport(collect([$evaluado->fresh('orden')]), true))->toHtmlTable();

        $this->assertStringContainsString('No liberado', $html);
        $this->assertStringNotContainsString('No Aprobado', $html);
    }
}
