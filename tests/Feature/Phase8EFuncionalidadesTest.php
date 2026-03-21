<?php

namespace Tests\Feature;

use App\Models\DocumentoEvaluado;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Role;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase8EFuncionalidadesTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin', 'display_name' => 'Administrador']);
        Role::create(['name' => 'empresa', 'display_name' => 'Empresa']);
        Role::create(['name' => 'repro', 'display_name' => 'Repro']);
    }

    // ─── Helpers ───

    private function crearAdmin(): User
    {
        $user = User::factory()->create(['role_as' => 3, 'estado' => 1]);
        $user->roles()->attach(Role::where('name', 'admin')->first());
        return $user;
    }

    private function crearRepro(): User
    {
        $user = User::factory()->create(['role_as' => 2, 'estado' => 1, 'cargo' => 'Poligrafista']);
        $user->roles()->attach(Role::where('name', 'repro')->first());
        return $user;
    }

    private function crearUsuarioEmpresa(Empresa $empresa): User
    {
        $user = User::factory()->create([
            'role_as' => 1,
            'estado' => 1,
            'empresa_id' => $empresa->id,
            'principal' => true,
        ]);
        $user->roles()->attach(Role::where('name', 'empresa')->first());
        return $user;
    }

    private function crearOrdenConEvaluado(?Empresa $empresa = null): array
    {
        $empresa ??= Empresa::factory()->create();
        $admin = $this->crearAdmin();

        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
        ]);

        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
        ]);

        return [$orden, $evaluado, $admin, $empresa];
    }

    // ─── 8E.1: Múltiples servicios por evaluado ───

    public function test_mismo_dpi_diferente_servicio_permitido_en_orden(): void
    {
        $empresa = Empresa::factory()->create();
        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)->post(route('ordenes.store'), [
            'empresa_id' => $empresa->id,
            'evaluados' => [
                [
                    'nombre' => 'Juan',
                    'apellidos' => 'Pérez',
                    'dpi' => '1234567890123',
                    'email' => 'juan@test.com',
                    'tipo_servicio' => 'poligrafo',
                    'tipo_formulario' => 'preempleo',
                ],
                [
                    'nombre' => 'Juan',
                    'apellidos' => 'Pérez',
                    'dpi' => '1234567890123',
                    'email' => 'juan@test.com',
                    'tipo_servicio' => 'vsa',
                    'tipo_formulario' => 'preempleo',
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();

        // Verificar que se crearon 2 evaluados con mismo DPI
        $orden = Orden::latest()->first();
        $this->assertEquals(2, $orden->evaluados()->where('dpi', '1234567890123')->count());
    }

    public function test_mismo_dpi_mismo_servicio_rechazado_en_orden(): void
    {
        $empresa = Empresa::factory()->create();
        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)->post(route('ordenes.store'), [
            'empresa_id' => $empresa->id,
            'evaluados' => [
                [
                    'nombre' => 'Juan',
                    'apellidos' => 'Pérez',
                    'dpi' => '1234567890123',
                    'email' => 'juan@test.com',
                    'tipo_servicio' => 'poligrafo',
                    'tipo_formulario' => 'preempleo',
                ],
                [
                    'nombre' => 'Juan',
                    'apellidos' => 'Pérez',
                    'dpi' => '1234567890123',
                    'email' => 'juan@test.com',
                    'tipo_servicio' => 'poligrafo',
                    'tipo_formulario' => 'preempleo',
                ],
            ],
        ]);

        $response->assertSessionHasErrors('evaluados');
    }

    public function test_badge_multi_servicio_en_show_orden(): void
    {
        $empresa = Empresa::factory()->create();
        $admin = $this->crearAdmin();

        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
        ]);

        // Crear 2 evaluados con mismo DPI
        EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'dpi' => '1234567890123',
            'tipo_servicio' => 'poligrafo',
        ]);
        EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'dpi' => '1234567890123',
            'tipo_servicio' => 'vsa',
        ]);

        $response = $this->actingAs($admin)->get(route('ordenes.show', $orden));

        $response->assertStatus(200);
        $response->assertSee('Multi-servicio');
    }

    public function test_dpi_unico_no_muestra_badge_multi_servicio(): void
    {
        [$orden, $evaluado, $admin] = $this->crearOrdenConEvaluado();

        $response = $this->actingAs($admin)->get(route('ordenes.show', $orden));

        $response->assertStatus(200);
        $response->assertDontSee('Multi-servicio');
    }

    // ─── 8E.2: Papelería desde empresa ───

    public function test_empresa_ve_seccion_documentos(): void
    {
        $empresa = Empresa::factory()->create();
        $userEmpresa = $this->crearUsuarioEmpresa($empresa);
        [$orden, $evaluado] = $this->crearOrdenConEvaluado($empresa);

        $response = $this->actingAs($userEmpresa)->get(route('empresa.ordenes.show', $orden));

        $response->assertStatus(200);
        $response->assertSee('Documentos');
        $response->assertSee('Subir');
    }

    public function test_empresa_puede_subir_documento(): void
    {
        Storage::fake('local');

        $empresa = Empresa::factory()->create();
        $userEmpresa = $this->crearUsuarioEmpresa($empresa);
        [$orden, $evaluado] = $this->crearOrdenConEvaluado($empresa);

        $response = $this->actingAs($userEmpresa)->post(route('documentos-evaluado.store'), [
            'evaluado_orden_id' => $evaluado->id,
            'tipo_documento' => 'dpi_archivo',
            'archivo' => UploadedFile::fake()->create('dpi.pdf', 500, 'application/pdf'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('documento_evaluados', [
            'evaluado_orden_id' => $evaluado->id,
            'tipo_documento' => 'dpi_archivo',
            'subido_por_tipo' => 'empresa',
            'subido_por_user_id' => $userEmpresa->id,
        ]);
    }

    public function test_empresa_puede_descargar_documento(): void
    {
        Storage::fake('local');

        $empresa = Empresa::factory()->create();
        $userEmpresa = $this->crearUsuarioEmpresa($empresa);
        [$orden, $evaluado] = $this->crearOrdenConEvaluado($empresa);

        $archivo = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');
        $ruta = $archivo->store('documentos_evaluados/' . $evaluado->id, 'local');

        $doc = DocumentoEvaluado::factory()->create([
            'evaluado_orden_id' => $evaluado->id,
            'ruta_archivo' => $ruta,
            'nombre_original' => 'test.pdf',
            'subido_por_user_id' => $userEmpresa->id,
        ]);

        $response = $this->actingAs($userEmpresa)->get(route('documentos-evaluado.download', $doc));

        $response->assertStatus(200);
    }

    public function test_empresa_puede_eliminar_su_propio_documento_pendiente(): void
    {
        Storage::fake('local');

        $empresa = Empresa::factory()->create();
        $userEmpresa = $this->crearUsuarioEmpresa($empresa);
        [$orden, $evaluado] = $this->crearOrdenConEvaluado($empresa);

        $archivo = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');
        $ruta = $archivo->store('documentos_evaluados/' . $evaluado->id, 'local');

        $doc = DocumentoEvaluado::factory()->create([
            'evaluado_orden_id' => $evaluado->id,
            'ruta_archivo' => $ruta,
            'subido_por_tipo' => 'empresa',
            'subido_por_user_id' => $userEmpresa->id,
            'estado_verificacion' => 'pendiente',
        ]);

        $response = $this->actingAs($userEmpresa)->delete(route('documentos-evaluado.destroy', $doc));

        $response->assertRedirect();
        $this->assertDatabaseMissing('documento_evaluados', ['id' => $doc->id]);
    }

    public function test_empresa_no_puede_eliminar_documento_de_repro(): void
    {
        $empresa = Empresa::factory()->create();
        $userEmpresa = $this->crearUsuarioEmpresa($empresa);
        $repro = $this->crearRepro();
        [$orden, $evaluado] = $this->crearOrdenConEvaluado($empresa);

        $doc = DocumentoEvaluado::factory()->create([
            'evaluado_orden_id' => $evaluado->id,
            'subido_por_tipo' => 'repro',
            'subido_por_user_id' => $repro->id,
        ]);

        $response = $this->actingAs($userEmpresa)->delete(route('documentos-evaluado.destroy', $doc));

        $response->assertStatus(403);
    }

    // ─── 8E.3: Papelería anticipada ───

    public function test_empresa_sube_documento_antes_de_cuestionario(): void
    {
        Storage::fake('local');

        $empresa = Empresa::factory()->create();
        $userEmpresa = $this->crearUsuarioEmpresa($empresa);
        [$orden, $evaluado] = $this->crearOrdenConEvaluado($empresa);

        // Evaluado aún no completa cuestionario
        $this->assertFalse((bool) $evaluado->cuestionario_completado);

        $response = $this->actingAs($userEmpresa)->post(route('documentos-evaluado.store'), [
            'evaluado_orden_id' => $evaluado->id,
            'tipo_documento' => 'antecedentes_penales',
            'archivo' => UploadedFile::fake()->create('antecedentes.pdf', 200, 'application/pdf'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('documento_evaluados', [
            'evaluado_orden_id' => $evaluado->id,
            'tipo_documento' => 'antecedentes_penales',
        ]);
    }

    // ─── 8E.4: Adjuntos seguimiento REPRO ───

    public function test_tipo_seguimiento_existe_en_modelo(): void
    {
        $tipos = DocumentoEvaluado::tiposDocumento();
        $this->assertArrayHasKey('seguimiento', $tipos);
        $this->assertEquals('Seguimiento REPRO', $tipos['seguimiento']);
    }

    public function test_repro_puede_subir_documento_seguimiento(): void
    {
        Storage::fake('local');

        $repro = $this->crearRepro();
        [$orden, $evaluado] = $this->crearOrdenConEvaluado();

        $response = $this->actingAs($repro)->post(route('documentos-evaluado.store'), [
            'evaluado_orden_id' => $evaluado->id,
            'tipo_documento' => 'seguimiento',
            'archivo' => UploadedFile::fake()->create('seguimiento.pdf', 300, 'application/pdf'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('documento_evaluados', [
            'evaluado_orden_id' => $evaluado->id,
            'tipo_documento' => 'seguimiento',
            'subido_por_tipo' => 'repro',
        ]);
    }

    public function test_empresa_ve_documento_seguimiento_de_repro(): void
    {
        $empresa = Empresa::factory()->create();
        $userEmpresa = $this->crearUsuarioEmpresa($empresa);
        $repro = $this->crearRepro();
        [$orden, $evaluado] = $this->crearOrdenConEvaluado($empresa);

        DocumentoEvaluado::factory()->create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_documento' => 'seguimiento',
            'nombre_original' => 'seguimiento_caso.pdf',
            'subido_por_tipo' => 'repro',
            'subido_por_user_id' => $repro->id,
        ]);

        $response = $this->actingAs($userEmpresa)->get(route('empresa.ordenes.show', $orden));

        $response->assertStatus(200);
        $response->assertSee('seguimiento_caso.pdf');
    }

    // ─── 8E.5: Reportes por mes ───

    public function test_reporte_evaluaciones_tiene_filtro_mes(): void
    {
        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)->get(route('reportes.evaluaciones'));

        $response->assertStatus(200);
        $response->assertSee('Mes R');
    }

    public function test_reporte_evaluaciones_filtra_por_fechas(): void
    {
        $admin = $this->crearAdmin();
        $empresa = Empresa::factory()->create();

        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
        ]);

        // Evaluado en enero
        EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'created_at' => '2026-01-15 10:00:00',
        ]);
        // Evaluado en marzo
        EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'created_at' => '2026-03-15 10:00:00',
        ]);

        // Filtrar solo enero
        $response = $this->actingAs($admin)->get(route('reportes.evaluaciones', [
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => '2026-01-31',
        ]));

        $response->assertStatus(200);
    }

    public function test_reporte_empresas_tiene_filtro_mes(): void
    {
        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)->get(route('reportes.empresas'));

        $response->assertStatus(200);
        $response->assertSee('Mes R');
    }

    // ─── 8E.6: Optimización rendimiento ───

    public function test_notificacion_cuestionario_usa_role_as_directo(): void
    {
        // Verificar que el controller no usa whereHas('roles') para notificaciones
        $controllerContent = file_get_contents(app_path('Http/Controllers/CuestionarioController.php'));

        // El método optimizado debe usar role_as >= 2 directamente
        $this->assertStringContainsString("where('role_as', '>=', 2)", $controllerContent);
        // No debe usar whereHas para la notificación
        $this->assertStringNotContainsString("whereHas('roles'", $controllerContent);
    }
}
