<?php

namespace Tests\Feature;

use App\Mail\CuestionarioCompletadoMail;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Role;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

/**
 * Tests para cubrir las observaciones del cliente recibidas 2026-04-22.
 *
 * Cubre: BUG-01, BUG-02, BUG-03, MEJ-04, MEJ-06, MEJ-07, MEJ-08.
 *
 * @see PROGRESS.md
 */
class ObservacionesClienteTest extends TestCase
{
    use RefreshDatabase, CreatesRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpRolesAndPermissions();
    }

    private function crearUsuarioEmpresa(?Empresa $empresa = null): User
    {
        $empresa = $empresa ?? Empresa::factory()->create();
        $user = User::factory()->create([
            'role_as' => 1,
            'empresa_id' => $empresa->id,
        ]);
        $user->roles()->attach(Role::where('name', 'empresa')->first());

        return $user;
    }

    // ──────────────────────────────────────────────────────────
    // BUG-01: Cliente no recibe 403 al ver su orden recién creada
    // ──────────────────────────────────────────────────────────

    public function test_bug01_cliente_puede_ver_orden_propia_tras_crearla(): void
    {
        $empresa = Empresa::factory()->create();
        $userEmpresa = $this->crearUsuarioEmpresa($empresa);

        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $userEmpresa->id,
        ]);

        $response = $this->actingAs($userEmpresa)
            ->get(route('empresa.ordenes.show', $orden));

        $response->assertStatus(200);
    }

    public function test_bug01_cliente_no_puede_ver_orden_de_otra_empresa(): void
    {
        $empresaA = Empresa::factory()->create();
        $empresaB = Empresa::factory()->create();
        $userEmpresaA = $this->crearUsuarioEmpresa($empresaA);

        $ordenB = Orden::factory()->create(['empresa_id' => $empresaB->id]);

        $response = $this->actingAs($userEmpresaA)
            ->get(route('empresa.ordenes.show', $ordenB));

        $response->assertStatus(403);
    }

    public function test_bug01_cliente_sin_empresa_id_es_redirigido_con_mensaje(): void
    {
        $user = User::factory()->create([
            'role_as' => 1,
            'empresa_id' => null,
        ]);
        $user->roles()->attach(Role::where('name', 'empresa')->first());

        $orden = Orden::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('empresa.ordenes.show', $orden));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }

    // ──────────────────────────────────────────────────────────
    // BUG-02: Cliente puede descargar PDF de su orden sin importar estado
    // ──────────────────────────────────────────────────────────

    public function test_bug02_cliente_puede_descargar_pdf_de_orden_en_solicitud(): void
    {
        $empresa = Empresa::factory()->create();
        $userEmpresa = $this->crearUsuarioEmpresa($empresa);

        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $userEmpresa->id,
            'estado' => 'orden_recibida',
            'resultados_visibles_empresa' => false,
        ]);

        $response = $this->actingAs($userEmpresa)
            ->get(route('ordenes.pdf', $orden));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_bug02_cliente_no_puede_descargar_pdf_de_orden_ajena(): void
    {
        $empresaA = Empresa::factory()->create();
        $empresaB = Empresa::factory()->create();
        $userEmpresaA = $this->crearUsuarioEmpresa($empresaA);

        $ordenB = Orden::factory()->create(['empresa_id' => $empresaB->id]);

        $response = $this->actingAs($userEmpresaA)
            ->get(route('ordenes.pdf', $ordenB));

        $response->assertStatus(403);
    }

    // ──────────────────────────────────────────────────────────
    // BUG-03: Reporte de Evaluaciones muestra evaluados de cliente
    // ──────────────────────────────────────────────────────────

    public function test_bug03_reporte_evaluaciones_muestra_ordenes_en_cualquier_estado(): void
    {
        $empresa = Empresa::factory()->create();
        $userEmpresa = $this->crearUsuarioEmpresa($empresa);

        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $userEmpresa->id,
            'estado' => 'orden_recibida',
            'resultados_visibles_empresa' => false,
        ]);
        EvaluadoOrden::factory()->create(['orden_id' => $orden->id]);

        $response = $this->actingAs($userEmpresa)
            ->get(route('reportes.evaluaciones'));

        $response->assertStatus(200);
        $response->assertViewHas('stats', function ($stats) {
            return $stats['total'] >= 1;
        });
    }

    public function test_bug03_reporte_evaluaciones_no_muestra_ordenes_de_otra_empresa(): void
    {
        $empresaA = Empresa::factory()->create();
        $empresaB = Empresa::factory()->create();
        $userEmpresaA = $this->crearUsuarioEmpresa($empresaA);

        $ordenB = Orden::factory()->create(['empresa_id' => $empresaB->id]);
        EvaluadoOrden::factory()->count(3)->create(['orden_id' => $ordenB->id]);

        $response = $this->actingAs($userEmpresaA)
            ->get(route('reportes.evaluaciones'));

        $response->assertStatus(200);
        $response->assertViewHas('stats', function ($stats) {
            return $stats['total'] === 0;
        });
    }

    // ──────────────────────────────────────────────────────────
    // MEJ-06: Cliente puede seleccionar sede de REPRO al crear orden
    // ──────────────────────────────────────────────────────────

    public function test_mej06_cliente_ve_selector_sede_en_form_crear(): void
    {
        $sede = Sede::factory()->create(['nombre' => 'Sede Central REPRO']);
        $userEmpresa = $this->crearUsuarioEmpresa();

        $response = $this->actingAs($userEmpresa)
            ->get(route('ordenes.create'));

        $response->assertStatus(200);
        $response->assertSee('Sede Responsable');
        $response->assertSee('Sede Central REPRO');
    }

    public function test_mej06_cliente_puede_guardar_orden_con_sede(): void
    {
        $sede = Sede::factory()->create();
        $empresa = Empresa::factory()->create();
        $userEmpresa = $this->crearUsuarioEmpresa($empresa);

        $response = $this->actingAs($userEmpresa)->post(route('ordenes.store'), [
            'empresa_id' => $empresa->id,
            'sede_id' => $sede->id,
            'evaluados' => [
                1 => [
                    'nombre' => 'Test',
                    'apellidos' => 'Cliente',
                    'dpi' => '3333333333333',
                    'email' => 'test@cliente.com',
                    'tipo_servicio' => 'poligrafo',
                    'tipo_formulario' => 'preempleo',
                ],
            ],
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('ordenes', [
            'empresa_id' => $empresa->id,
            'sede_id' => $sede->id,
        ]);
    }

    public function test_mej06_sede_inactiva_es_rechazada(): void
    {
        $sede = Sede::factory()->inactiva()->create();
        $empresa = Empresa::factory()->create();
        $userEmpresa = $this->crearUsuarioEmpresa($empresa);

        $response = $this->actingAs($userEmpresa)->post(route('ordenes.store'), [
            'empresa_id' => $empresa->id,
            'sede_id' => $sede->id,
            'evaluados' => [
                1 => [
                    'nombre' => 'Test',
                    'apellidos' => 'Cliente',
                    'dpi' => '4444444444444',
                    'email' => 'test2@cliente.com',
                    'tipo_servicio' => 'poligrafo',
                    'tipo_formulario' => 'preempleo',
                ],
            ],
        ]);

        $response->assertSessionHasErrors('sede_id');
    }

    // ──────────────────────────────────────────────────────────
    // MEJ-08: NO se envía email al completar cuestionario (solo in-app)
    // ──────────────────────────────────────────────────────────

    public function test_mej08_no_se_envia_email_al_completar_cuestionario(): void
    {
        Mail::fake();

        // Crear admin/repro para que reciba notificación in-app
        $admin = User::factory()->create([
            'role_as' => 3,
            'estado' => 1,
            'email' => 'admin@repro.com',
        ]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);
        $evaluado = EvaluadoOrden::factory()->create(['orden_id' => $orden->id]);

        // Invocar el método privado vía reflection
        $controller = new \App\Http\Controllers\CuestionarioController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('notificarCuestionarioCompletado');
        $method->setAccessible(true);
        $method->invoke($controller, $evaluado);

        // No debe haberse encolado/enviado el correo
        Mail::assertNotQueued(CuestionarioCompletadoMail::class);
        Mail::assertNotSent(CuestionarioCompletadoMail::class);

        // Sí debe existir la notificación in-app en BD
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $admin->id,
            'type' => \App\Notifications\CuestionarioCompletadoNotification::class,
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // MEJ-04: Tras crear orden, mostrar banner para subir papelería
    // ──────────────────────────────────────────────────────────

    public function test_mej04_redirect_post_store_incluye_flag_papeleria(): void
    {
        $empresa = Empresa::factory()->create();
        $userEmpresa = $this->crearUsuarioEmpresa($empresa);

        $response = $this->actingAs($userEmpresa)->post(route('ordenes.store'), [
            'empresa_id' => $empresa->id,
            'evaluados' => [
                1 => [
                    'nombre' => 'Test',
                    'apellidos' => 'Papel',
                    'dpi' => '5555555555555',
                    'email' => 'pap@test.com',
                    'tipo_servicio' => 'poligrafo',
                    'tipo_formulario' => 'preempleo',
                ],
            ],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('mostrar_papeleria', true);
        $response->assertSessionHas('success');
    }

    public function test_mej04_vista_show_muestra_banner_papeleria(): void
    {
        $empresa = Empresa::factory()->create();
        $userEmpresa = $this->crearUsuarioEmpresa($empresa);
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $userEmpresa->id,
        ]);

        $response = $this->actingAs($userEmpresa)
            ->withSession([
                'success' => 'Orden creada exitosamente.',
                'mostrar_papeleria' => true,
            ])
            ->get(route('empresa.ordenes.show', $orden));

        $response->assertStatus(200);
        $response->assertSee('Próximo paso');
        $response->assertSee('seccion-evaluados', false);
    }

    // ──────────────────────────────────────────────────────────
    // MEJ-07: Listado de órdenes muestra nombre del primer evaluado
    // ──────────────────────────────────────────────────────────

    public function test_mej07_listado_empresa_muestra_nombre_primer_evaluado(): void
    {
        $empresa = Empresa::factory()->create();
        $userEmpresa = $this->crearUsuarioEmpresa($empresa);
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);
        EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'nombre' => 'Juan Carlos',
            'apellidos' => 'González',
        ]);

        $response = $this->actingAs($userEmpresa)
            ->get(route('empresa.ordenes.index'));

        $response->assertStatus(200);
        $response->assertSee($orden->codigo_orden);
        $response->assertSee('Juan Carlos González');
    }

    public function test_mej07_listado_admin_muestra_nombre_primer_evaluado(): void
    {
        $admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        $orden = Orden::factory()->create();
        EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'nombre' => 'María',
            'apellidos' => 'López',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('ordenes.index'));

        $response->assertStatus(200);
        $response->assertSee('María López');
    }
}
