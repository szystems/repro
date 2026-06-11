<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Role;
use App\Models\Sede;
use App\Models\User;
use App\Mail\NuevaOrdenSedeMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

class Phase8CEstadosUXTest extends TestCase
{
    use RefreshDatabase, WithFaker, CreatesRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
    }

    // ─── Helpers ───

    private function crearAdmin(): User
    {
        $user = User::factory()->create(['role_as' => 3]);
        $user->roles()->attach(Role::where('name', 'admin')->first());
        return $user;
    }

    private function crearRepro(?Sede $sede = null): User
    {
        $user = User::factory()->create([
            'role_as' => 2,
            'sede_id' => $sede?->id,
        ]);
        $user->roles()->attach(Role::where('name', 'repro')->first());
        return $user;
    }

    private function crearEmpresa(?Empresa $empresa = null): User
    {
        $empresa ??= Empresa::factory()->create();
        $user = User::factory()->create([
            'role_as' => 1,
            'empresa_id' => $empresa->id,
            'principal' => true,
        ]);
        $user->roles()->attach(Role::where('name', 'empresa')->first());
        return $user;
    }

    // ─── 8C.1 + 8C.2: Estados con colores en vista empresa ───

    public function test_empresa_index_muestra_estado_con_color_dinamico(): void
    {
        $empresa = Empresa::factory()->create();
        $user = $this->crearEmpresa($empresa);
        $admin = $this->crearAdmin();

        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
            'estado' => 'en_proceso',
        ]);

        $response = $this->actingAs($user)->get(route('empresa.ordenes.index'));
        $response->assertStatus(200);
        $response->assertSee($orden->estado_human);
    }

    public function test_empresa_show_muestra_estado_evaluacion_y_formulario(): void
    {
        $empresa = Empresa::factory()->create();
        $user = $this->crearEmpresa($empresa);
        $admin = $this->crearAdmin();

        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
        ]);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'estado_evaluacion' => 'pendiente_de_evaluacion',
            'estado_formulario' => 'link_pendiente',
        ]);

        $response = $this->actingAs($user)->get(route('empresa.ordenes.show', $orden));
        $response->assertStatus(200);
        $response->assertSee($evaluado->estado_evaluacion_texto);
    }

    // ─── 8C.3: Observaciones visibles ───

    public function test_empresa_show_muestra_observaciones_evaluado(): void
    {
        $empresa = Empresa::factory()->create();
        $user = $this->crearEmpresa($empresa);
        $admin = $this->crearAdmin();

        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
        ]);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'observaciones' => 'Observación de prueba visible',
        ]);

        $response = $this->actingAs($user)->get(route('empresa.ordenes.show', $orden));
        $response->assertStatus(200);
        $response->assertSee('Observación de prueba visible');
    }

    // ─── 8C.5: Botón reenviar enlace ───

    public function test_empresa_show_tiene_boton_reenviar_enlace(): void
    {
        $empresa = Empresa::factory()->create();
        $user = $this->crearEmpresa($empresa);
        $admin = $this->crearAdmin();

        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
        ]);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'email' => 'eval@test.com',
            'cuestionario_completado' => false,
        ]);

        $response = $this->actingAs($user)->get(route('empresa.ordenes.show', $orden));
        $response->assertStatus(200);
        $response->assertSee('reenviar-correo');
    }

    // ─── 8C.6: WhatsApp y Maps en sedes ───

    public function test_sede_puede_tener_whatsapp_y_enlace_maps(): void
    {
        $sede = Sede::factory()->create([
            'whatsapp' => '50212345678',
            'enlace_maps' => 'https://maps.google.com/test',
        ]);

        $this->assertDatabaseHas('sedes', [
            'id' => $sede->id,
            'whatsapp' => '50212345678',
            'enlace_maps' => 'https://maps.google.com/test',
        ]);
    }

    public function test_admin_show_sede_muestra_whatsapp_y_maps(): void
    {
        $admin = $this->crearAdmin();
        $sede = Sede::factory()->create([
            'whatsapp' => '50212345678',
            'enlace_maps' => 'https://maps.google.com/test',
        ]);

        $response = $this->actingAs($admin)->get(route('sedes.show', $sede));
        $response->assertStatus(200);
        $response->assertSee('50212345678');
        $response->assertSee('Ver en Maps');
    }

    // ─── 8C.7: Sede responsable en orden ───

    public function test_orden_puede_tener_sede_responsable(): void
    {
        $admin = $this->crearAdmin();
        $sede = Sede::factory()->create();
        $empresa = Empresa::factory()->create();

        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
            'sede_id' => $sede->id,
        ]);

        $this->assertDatabaseHas('ordenes', [
            'id' => $orden->id,
            'sede_id' => $sede->id,
        ]);

        $this->assertEquals($sede->id, $orden->sede->id);
    }

    public function test_admin_show_orden_muestra_sede_responsable(): void
    {
        $admin = $this->crearAdmin();
        $sede = Sede::factory()->create(['nombre' => 'Sede Central Test']);
        $empresa = Empresa::factory()->create();

        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
            'sede_id' => $sede->id,
        ]);

        $response = $this->actingAs($admin)->get(route('ordenes.show', $orden));
        $response->assertStatus(200);
        $response->assertSee('Sede Central Test');
    }

    // ─── 8C.9: Filtro por sede ───

    public function test_admin_puede_filtrar_ordenes_por_sede(): void
    {
        $admin = $this->crearAdmin();
        $sede = Sede::factory()->create();
        $empresa = Empresa::factory()->create();

        $ordenConSede = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
            'sede_id' => $sede->id,
        ]);
        $ordenSinSede = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
            'sede_id' => null,
        ]);

        $response = $this->actingAs($admin)->get(route('ordenes.index', ['sede_id' => $sede->id]));
        $response->assertStatus(200);
        $response->assertSee($ordenConSede->codigo_orden);
        $response->assertDontSee($ordenSinSede->codigo_orden);
    }

    // ─── 8C.10: Modalidad de cita ───

    public function test_evaluado_puede_tener_modalidad(): void
    {
        $admin = $this->crearAdmin();
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
        ]);

        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'modalidad' => 'presencial',
        ]);

        $this->assertDatabaseHas('evaluados_orden', [
            'id' => $evaluado->id,
            'modalidad' => 'presencial',
        ]);
    }

    public function test_modalidad_puede_ser_virtual(): void
    {
        $admin = $this->crearAdmin();
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
        ]);

        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'modalidad' => 'virtual',
        ]);

        $this->assertEquals('virtual', $evaluado->fresh()->modalidad);
    }

    // ─── 8C.11: Sede en usuarios REPRO ───

    public function test_usuario_repro_puede_tener_sede_asignada(): void
    {
        $sede = Sede::factory()->create();
        $user = $this->crearRepro($sede);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'sede_id' => $sede->id,
        ]);

        $this->assertEquals($sede->id, $user->sede->id);
    }

    public function test_admin_puede_crear_usuario_repro_con_sede(): void
    {
        $admin = $this->crearAdmin();
        $sede = Sede::factory()->create();
        $reproRole = Role::where('name', 'repro')->firstOrFail();

        $response = $this->actingAs($admin)->post(url('insert-user'), [
            'name' => 'Test Repro',
            'email' => 'repro-test@example.com',
            'fecha_nacimiento' => '1990-01-01',
            'role_id' => $reproRole->id,
            'sede_id' => $sede->id,
            'cargo' => 'Poligrafista',
        ]);

        $response->assertRedirect('users');
        $this->assertDatabaseHas('users', [
            'email' => 'repro-test@example.com',
            'sede_id' => $sede->id,
        ]);
    }

    public function test_admin_show_usuario_repro_muestra_sede(): void
    {
        $admin = $this->crearAdmin();
        $sede = Sede::factory()->create(['nombre' => 'Sede Admin Test']);
        $repro = $this->crearRepro($sede);

        $response = $this->actingAs($admin)->get(url('show-user/' . $repro->id));
        $response->assertStatus(200);
        $response->assertSee('Sede Admin Test');
    }

    // ─── 8C.12: Notificación nueva orden a sede ───

    public function test_nueva_orden_con_sede_envia_notificacion(): void
    {
        Mail::fake();

        $admin = $this->crearAdmin();
        $sede = Sede::factory()->create();
        $empresa = Empresa::factory()->create();
        $reproSede = $this->crearRepro($sede);

        $response = $this->actingAs($admin)->post(route('ordenes.store'), [
            'empresa_id' => $empresa->id,
            'sede_id' => $sede->id,
            'prioridad' => 'normal',
            'evaluados' => [
                [
                    'nombre' => 'Test',
                    'apellidos' => 'Evaluado',
                    'dpi' => '1234567890123',
                    'email' => 'eval@test.com',
                    'tipo_servicio' => 'poligrafo',
                    'tipo_formulario' => 'preempleo',
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        Mail::assertQueued(NuevaOrdenSedeMail::class);
    }

    public function test_nueva_orden_sin_sede_no_envia_notificacion(): void
    {
        Mail::fake();

        $admin = $this->crearAdmin();
        $empresa = Empresa::factory()->create();

        $response = $this->actingAs($admin)->post(route('ordenes.store'), [
            'empresa_id' => $empresa->id,
            'prioridad' => 'normal',
            'evaluados' => [
                [
                    'nombre' => 'Test',
                    'apellidos' => 'Sin Sede',
                    'dpi' => '9876543210123',
                    'email' => 'sin-sede@test.com',
                    'tipo_servicio' => 'poligrafo',
                    'tipo_formulario' => 'preempleo',
                ],
            ],
        ]);

        Mail::assertNotQueued(NuevaOrdenSedeMail::class);
    }

    public function test_resultado_preliminar_existe_en_catalogo_de_estados(): void
    {
        $estados = EvaluadoOrden::estadosEvaluacionDisponibles();

        $this->assertArrayHasKey('resultado_preliminar', $estados);
        $this->assertEquals('Resultado Preliminar', $estados['resultado_preliminar']);
    }

    public function test_transicion_en_proceso_a_en_revision_es_valida(): void
    {
        // Flujo correcto: en_proceso → en_revision (no salta directo a resultado_preliminar)
        $transiciones = EvaluadoOrden::transicionesEvaluacion();

        $this->assertContains('en_revision', $transiciones['en_proceso']);
    }

    public function test_transicion_en_revision_a_resultado_preliminar_es_valida(): void
    {
        $transiciones = EvaluadoOrden::transicionesEvaluacion();

        $this->assertContains('resultado_preliminar', $transiciones['en_revision']);
    }

    public function test_transicion_resultado_preliminar_a_informe_final_enviado_es_valida(): void
    {
        $transiciones = EvaluadoOrden::transicionesEvaluacion();

        $this->assertContains('informe_final_enviado', $transiciones['resultado_preliminar']);
    }

    public function test_estado_resultado_preliminar_tiene_texto_y_color(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => Orden::factory()->create()->id,
            'estado_evaluacion' => 'resultado_preliminar',
        ]);

        $this->assertEquals('Resultado Preliminar', $evaluado->estado_evaluacion_texto);
        $this->assertNotEmpty($evaluado->estado_evaluacion_color);
    }

    public function test_puede_cambiar_estado_de_en_proceso_a_en_revision(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => Orden::factory()->create()->id,
            'estado_evaluacion' => 'en_proceso',
        ]);

        $resultado = $evaluado->cambiarEstadoEvaluacion('en_revision');

        $this->assertTrue($resultado);
        $this->assertEquals('en_revision', $evaluado->fresh()->estado_evaluacion);
    }

    public function test_puede_cambiar_estado_de_resultado_preliminar_a_informe_final_enviado(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => Orden::factory()->create()->id,
            'estado_evaluacion' => 'resultado_preliminar',
        ]);

        $resultado = $evaluado->cambiarEstadoEvaluacion('informe_final_enviado');

        $this->assertTrue($resultado);
        $this->assertEquals('informe_final_enviado', $evaluado->fresh()->estado_evaluacion);
    }
}
