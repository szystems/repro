<?php

namespace Tests\Feature;

use App\Models\Config;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class Phase8BAjustesTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin', 'display_name' => 'Administrador']);
        Role::create(['name' => 'empresa', 'display_name' => 'Empresa']);
        Role::create(['name' => 'repro', 'display_name' => 'Repro']);
    }

    // ─── 8B.1: "Estado de Cuestionarios" → "Estado de Procesos" ───

    public function test_sidebar_empresa_muestra_estado_de_procesos(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create([
            'role_as' => 1,
            'empresa_id' => $empresa->id,
            'principal' => true,
        ]);
        $user->roles()->attach(Role::where('name', 'empresa')->first());

        $response = $this->actingAs($user)->get(url('empresa/cuestionarios'));
        $response->assertStatus(200);
        $response->assertSee('Estado de Procesos');
        $response->assertDontSee('Estado de Cuestionarios');
    }

    // ─── 8B.2: Nombres de PDF incluyen nombre evaluado + código orden ───

    public function test_pdf_nombre_archivo_cuestionario_incluye_nombre_y_orden(): void
    {
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
            'codigo_orden' => 'ORD-TEST-001',
        ]);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'nombre' => 'Juan',
            'apellidos' => 'Pérez',
        ]);

        // Create a minimal cuestionario
        $cuestionario = \App\Models\Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'total_secciones' => 1,
            'seccion_actual' => 1,
            'progreso_secciones' => [1 => true],
            'completado' => true,
            'completado_at' => now(),
            'estado' => 'completado',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.cuestionarios.pdf', $cuestionario->id));

        $response->assertStatus(200);
        $contentDisposition = $response->headers->get('content-disposition');
        $this->assertStringContainsString('Juan', $contentDisposition);
        $this->assertStringContainsString('rez', $contentDisposition); // Pérez (encoded)
        $this->assertStringContainsString('ORD-TEST-001', $contentDisposition);
    }

    public function test_pdf_nombre_archivo_orden_incluye_codigo_y_empresa(): void
    {
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());
        $empresa = Empresa::factory()->create(['nombre' => 'EmpresaTest']);
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
            'codigo_orden' => 'ORD-PDF-002',
        ]);

        $response = $this->actingAs($admin)->get(route('ordenes.pdf', $orden->id));
        $response->assertStatus(200);
        $contentDisposition = $response->headers->get('content-disposition');
        $this->assertStringContainsString('ORD-PDF-002', $contentDisposition);
        $this->assertStringContainsString('EmpresaTest', $contentDisposition);
    }

    // ─── 8B.3: Filtro empresa en reporte de empresas ───

    public function test_reporte_empresas_filtra_por_empresa_id(): void
    {
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());
        $empresa1 = Empresa::factory()->create(['nombre' => 'AlphaUniqueXYZ']);
        $empresa2 = Empresa::factory()->create(['nombre' => 'BetaUniqueXYZ']);

        // Without filter — both appear in table
        $response = $this->actingAs($admin)->get(route('reportes.empresas'));
        $response->assertStatus(200);
        $response->assertSee('AlphaUniqueXYZ');
        $response->assertSee('BetaUniqueXYZ');

        // With filter — only Alpha in table body
        $response = $this->actingAs($admin)
            ->get(route('reportes.empresas', ['empresa_id' => $empresa1->id]));
        $response->assertStatus(200);
        $response->assertSee('AlphaUniqueXYZ');
        // BetaUniqueXYZ appears only in the filter select option, not in the table
        $content = $response->getContent();
        // Count occurrences: should appear exactly once (in the select dropdown), not in the table
        $occurrences = substr_count($content, 'BetaUniqueXYZ');
        $this->assertLessThanOrEqual(1, $occurrences, 'BetaUniqueXYZ should only appear in filter select, not in the results table');
    }

    public function test_reporte_empresas_muestra_select_de_empresas(): void
    {
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());
        $empresa = Empresa::factory()->create(['nombre' => 'MiEmpresaFiltro']);

        $response = $this->actingAs($admin)->get(route('reportes.empresas'));
        $response->assertStatus(200);
        $response->assertSee('MiEmpresaFiltro');
    }

    // ─── 8B.4: fecha_limite oculta, muestra fecha de creación ───

    public function test_vista_show_orden_muestra_fecha_creacion_en_lugar_de_limite(): void
    {
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('ordenes.show', $orden));
        $response->assertStatus(200);
        $response->assertSee('Fecha de Creación');
        $response->assertDontSee('Fecha Límite');
    }

    public function test_create_orden_no_muestra_campo_fecha_limite(): void
    {
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());
        Empresa::factory()->create();

        $response = $this->actingAs($admin)->get(route('ordenes.create'));
        $response->assertStatus(200);
        $response->assertDontSee('Fecha Límite');
    }

    // ─── 8B.6: Historial DPI accesible desde menú ───

    public function test_historial_dpi_accesible_para_admin(): void
    {
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        $response = $this->actingAs($admin)
            ->get(route('admin.cuestionarios.historial-dpi'));
        $response->assertStatus(200);
        $response->assertSee('Historial por DPI');
    }

    public function test_historial_dpi_busca_evaluado_por_dpi(): void
    {
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
        ]);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'nombre' => 'Carlos',
            'apellidos' => 'López',
            'dpi' => '1234567890123',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.cuestionarios.historial-dpi', ['dpi' => '1234567890123']));
        $response->assertStatus(200);
        $response->assertSee('Carlos');
        $response->assertSee('López');
    }

    public function test_historial_dpi_sin_resultados(): void
    {
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        $response = $this->actingAs($admin)
            ->get(route('admin.cuestionarios.historial-dpi', ['dpi' => '9999999999999']));
        $response->assertStatus(200);
        $response->assertSee('No se encontraron registros');
    }

    public function test_sidebar_admin_muestra_enlace_historial_dpi(): void
    {
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        $response = $this->actingAs($admin)->get(url('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Historial por DPI');
    }

    // ─── 8B.7: Dirección del evaluado ───

    public function test_crear_orden_con_direccion_evaluado(): void
    {
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());
        $empresa = Empresa::factory()->create();

        $response = $this->actingAs($admin)->post(route('ordenes.store'), [
            'empresa_id' => $empresa->id,
            'evaluados' => [
                1 => [
                    'nombre' => 'Ana',
                    'apellidos' => 'Martínez',
                    'dpi' => '1234567890123',
                    'email' => 'ana@test.com',
                    'tipo_servicio' => 'poligrafo',
                    'tipo_formulario' => 'preempleo',
                    'telefono' => '55551234',
                    'direccion' => '5ta Avenida 12-34 Zona 1, Guatemala',
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('evaluados_orden', [
            'nombre' => 'Ana',
            'direccion' => '5ta Avenida 12-34 Zona 1, Guatemala',
        ]);
    }

    public function test_editar_orden_actualiza_direccion_evaluado(): void
    {
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
        ]);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'direccion' => null,
        ]);

        $response = $this->actingAs($admin)->put(route('ordenes.update', $orden), [
            'empresa_id' => $empresa->id,
            'evaluados' => [
                0 => [
                    'id' => $evaluado->id,
                    'nombre' => $evaluado->nombre,
                    'apellidos' => $evaluado->apellidos,
                    'dpi' => $evaluado->dpi,
                    'email' => $evaluado->email,
                    'tipo_servicio' => $evaluado->tipo_servicio,
                    'tipo_formulario' => $evaluado->tipo_formulario,
                    'direccion' => '6ta Calle 7-89 Zona 10',
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('evaluados_orden', [
            'id' => $evaluado->id,
            'direccion' => '6ta Calle 7-89 Zona 10',
        ]);
    }

    public function test_show_orden_muestra_direccion_evaluado(): void
    {
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
        ]);
        EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'direccion' => 'Boulevard Vista Hermosa 25-11',
        ]);

        $response = $this->actingAs($admin)->get(route('ordenes.show', $orden));
        $response->assertStatus(200);
        $response->assertSee('Boulevard Vista Hermosa 25-11');
    }

    // ─── 8B.8: Observaciones por evaluado ───

    public function test_crear_orden_con_observaciones_evaluado(): void
    {
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());
        $empresa = Empresa::factory()->create();

        $response = $this->actingAs($admin)->post(route('ordenes.store'), [
            'empresa_id' => $empresa->id,
            'evaluados' => [
                1 => [
                    'nombre' => 'Pedro',
                    'apellidos' => 'Ramírez',
                    'dpi' => '9876543210123',
                    'email' => 'pedro@test.com',
                    'tipo_servicio' => 'vsa',
                    'tipo_formulario' => 'preempleo',
                    'observaciones' => 'Evaluado requiere atención especial',
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('evaluados_orden', [
            'nombre' => 'Pedro',
            'observaciones' => 'Evaluado requiere atención especial',
        ]);
    }

    public function test_show_orden_muestra_observaciones_evaluado(): void
    {
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
        ]);
        EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'observaciones' => 'Nota importante sobre este evaluado',
        ]);

        $response = $this->actingAs($admin)->get(route('ordenes.show', $orden));
        $response->assertStatus(200);
        $response->assertSee('Nota importante sobre este evaluado');
    }
}
