<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests de auditoría preventiva — Sprint 1.
 *
 * Cubre hallazgos críticos detectados en la auditoría 2026-04-22:
 * - H-01: throttling en rutas públicas de cuestionario
 * - H-03: REPRO puede eliminar órdenes
 * - H-04: token vencido bloquea endpoints públicos del cuestionario
 *
 * @see PROGRESS.md
 */
class AuditoriaSeguridadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin', 'display_name' => 'Administrador']);
        Role::create(['name' => 'empresa', 'display_name' => 'Empresa']);
        Role::create(['name' => 'repro', 'display_name' => 'Repro']);
    }

    private function crearAdmin(): User
    {
        $user = User::factory()->create(['role_as' => 3, 'estado' => 1]);
        $user->roles()->attach(Role::where('name', 'admin')->first());
        return $user;
    }

    private function crearRepro(): User
    {
        $user = User::factory()->create(['role_as' => 2, 'estado' => 1]);
        $reproRole = Role::where('name', 'repro')->first();
        // Asignar permiso ordenes.eliminar al rol repro para los tests
        $permission = Permission::firstOrCreate(
            ['name' => 'ordenes.eliminar'],
            ['display_name' => 'Eliminar Órdenes', 'module' => 'ordenes']
        );
        $reproRole->givePermission($permission);
        $user->roles()->attach($reproRole);
        return $user;
    }

    private function crearEmpresaUser(): User
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create([
            'role_as' => 1,
            'empresa_id' => $empresa->id,
        ]);
        $user->roles()->attach(Role::where('name', 'empresa')->first());
        return $user;
    }

    // ──────────────────────────────────────────────────────────
    // H-03: REPRO puede eliminar órdenes
    // ──────────────────────────────────────────────────────────

    public function test_h03_repro_puede_eliminar_orden(): void
    {
        $repro = $this->crearRepro();
        $orden = Orden::factory()->create(['estado' => 'solicitud']);

        $response = $this->actingAs($repro)
            ->delete(route('ordenes.destroy', $orden));

        $response->assertRedirect(route('ordenes.index'));
        $this->assertDatabaseMissing('ordenes', ['id' => $orden->id]);
    }

    public function test_h03_admin_puede_eliminar_orden(): void
    {
        $admin = $this->crearAdmin();
        $orden = Orden::factory()->create(['estado' => 'solicitud']);

        $response = $this->actingAs($admin)
            ->delete(route('ordenes.destroy', $orden));

        $response->assertRedirect(route('ordenes.index'));
        $this->assertDatabaseMissing('ordenes', ['id' => $orden->id]);
    }

    public function test_h03_empresa_no_puede_eliminar_orden(): void
    {
        $empresaUser = $this->crearEmpresaUser();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresaUser->empresa_id,
            'estado' => 'solicitud',
        ]);

        $response = $this->actingAs($empresaUser)
            ->delete(route('ordenes.destroy', $orden));

        $response->assertForbidden();
        $this->assertDatabaseHas('ordenes', ['id' => $orden->id]);
    }

    // ──────────────────────────────────────────────────────────
    // H-04: token vencido bloquea endpoints públicos
    // ──────────────────────────────────────────────────────────

    public function test_h04_token_vencido_bloquea_verificar_identidad(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'dpi' => '1234567890123',
            'token_unico' => str_repeat('a', 64),
            'token_expira_at' => now()->subDay(),
        ]);

        $response = $this->post(
            route('cuestionario.verificar', $evaluado->token_unico),
            ['dpi_ingresado' => '1234567890123']
        );

        $response->assertNotFound();
    }

    public function test_h04_token_vencido_bloquea_guardar_seccion(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'token_unico' => str_repeat('b', 64),
            'token_expira_at' => now()->subHour(),
        ]);

        $response = $this->post(
            route('cuestionario.guardar-seccion', [
                'token' => $evaluado->token_unico,
                'numero' => 1,
            ]),
            []
        );

        $response->assertNotFound();
    }

    public function test_h04_token_vencido_bloquea_finalizar(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'token_unico' => str_repeat('c', 64),
            'token_expira_at' => now()->subMinute(),
        ]);

        $response = $this->get(
            route('cuestionario.finalizar', $evaluado->token_unico)
        );

        $response->assertNotFound();
    }

    public function test_h04_token_vencido_bloquea_completar(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'token_unico' => str_repeat('d', 64),
            'token_expira_at' => now()->subDays(31),
        ]);

        $response = $this->post(
            route('cuestionario.completar', $evaluado->token_unico),
            ['confirmacion_final' => 1]
        );

        $response->assertNotFound();
    }

    // ──────────────────────────────────────────────────────────
    // H-01: rate limiting en rutas públicas de cuestionario
    // ──────────────────────────────────────────────────────────

    public function test_h01_throttle_aplica_a_verificar_identidad(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'dpi' => '1234567890123',
            'token_unico' => str_repeat('e', 64),
            'token_expira_at' => now()->addDays(7),
        ]);

        // El throttle es 60/min. Hacemos 61 requests.
        for ($i = 0; $i < 60; $i++) {
            $this->post(
                route('cuestionario.verificar', $evaluado->token_unico),
                ['dpi_ingresado' => '0000000000000']
            );
        }

        $response = $this->post(
            route('cuestionario.verificar', $evaluado->token_unico),
            ['dpi_ingresado' => '0000000000000']
        );

        $response->assertStatus(429); // Too Many Requests
    }

    // ──────────────────────────────────────────────────────────
    // H-02: índice único compuesto (orden+dpi+servicio)
    // ──────────────────────────────────────────────────────────

    public function test_h02_no_permite_evaluado_duplicado_dpi_servicio_en_misma_orden(): void
    {
        $orden = Orden::factory()->create();
        EvaluadoOrden::factory()->create([
            'orden_id'      => $orden->id,
            'dpi'           => '1111111111111',
            'tipo_servicio' => 'poligrafo',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        EvaluadoOrden::factory()->create([
            'orden_id'      => $orden->id,
            'dpi'           => '1111111111111',
            'tipo_servicio' => 'poligrafo',
        ]);
    }

    public function test_h02_permite_mismo_dpi_con_distinto_servicio_en_misma_orden(): void
    {
        $orden = Orden::factory()->create();
        EvaluadoOrden::factory()->create([
            'orden_id'      => $orden->id,
            'dpi'           => '2222222222222',
            'tipo_servicio' => 'poligrafo',
        ]);

        $segundo = EvaluadoOrden::factory()->create([
            'orden_id'      => $orden->id,
            'dpi'           => '2222222222222',
            'tipo_servicio' => 'vsa',
        ]);

        $this->assertDatabaseHas('evaluados_orden', ['id' => $segundo->id]);
    }

    // ──────────────────────────────────────────────────────────
    // H-05: DocumentoEvaluadoRequest::authorize() valida pertenencia
    // ──────────────────────────────────────────────────────────

    public function test_h05_empresa_no_puede_subir_documento_a_evaluado_de_otra_empresa(): void
    {
        $empresaUserA = $this->crearEmpresaUser();
        $empresaB = Empresa::factory()->create();
        $ordenB = Orden::factory()->create(['empresa_id' => $empresaB->id]);
        $evaluadoB = EvaluadoOrden::factory()->create(['orden_id' => $ordenB->id]);

        $archivo = \Illuminate\Http\UploadedFile::fake()->create('doc.pdf', 50, 'application/pdf');

        $response = $this->actingAs($empresaUserA)
            ->post(route('documentos-evaluado.store'), [
                'evaluado_orden_id' => $evaluadoB->id,
                'tipo_documento'    => array_key_first(\App\Models\DocumentoEvaluado::tiposDocumento()),
                'archivo'           => $archivo,
            ]);

        $response->assertForbidden();
    }

    // ──────────────────────────────────────────────────────────
    // H-08: email duplicado en misma orden
    // ──────────────────────────────────────────────────────────

    public function test_h08_no_permite_email_duplicado_en_misma_orden(): void
    {
        $admin = $this->crearAdmin();
        $empresa = Empresa::factory()->create();

        $payload = [
            'empresa_id' => $empresa->id,
            'evaluados'  => [
                [
                    'nombre'           => 'Uno',
                    'apellidos'        => 'Persona',
                    'dpi'              => '3333333333333',
                    'email'            => 'mismo@correo.com',
                    'tipo_servicio'    => 'poligrafo',
                    'tipo_formulario'  => 'preempleo',
                ],
                [
                    'nombre'           => 'Dos',
                    'apellidos'        => 'Persona',
                    'dpi'              => '4444444444444',
                    'email'            => 'mismo@correo.com',
                    'tipo_servicio'    => 'poligrafo',
                    'tipo_formulario'  => 'preempleo',
                ],
            ],
        ];

        $response = $this->actingAs($admin)
            ->post(route('ordenes.store'), $payload);

        $response->assertSessionHasErrors('evaluados');
        $this->assertFalse(
            \App\Models\Orden::query()->whereHas('evaluados', fn($q) => $q->where('email', 'mismo@correo.com'))->exists()
        );
    }

    // ──────────────────────────────────────────────────────────
    // H-10: Audit trail de transiciones de estado
    // ──────────────────────────────────────────────────────────

    public function test_h10_orden_cambia_estado_se_registra_en_auditoria(): void
    {
        $admin = $this->crearAdmin();
        $orden = Orden::factory()->create(['estado' => 'solicitud']);

        $this->actingAs($admin);
        $orden->cambiarEstado('autorizacion');

        $this->assertDatabaseHas('auditoria_estados', [
            'entidad_tipo' => Orden::class,
            'entidad_id' => $orden->id,
            'campo' => 'estado',
            'estado_anterior' => 'solicitud',
            'estado_nuevo' => 'autorizacion',
            'user_id' => $admin->id,
        ]);
    }

    public function test_h10_evaluado_cambia_estado_evaluacion_se_registra(): void
    {
        $admin = $this->crearAdmin();
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'estado_evaluacion' => 'pendiente',
        ]);

        $this->actingAs($admin);
        $evaluado->cambiarEstadoEvaluacion('contactando');

        $this->assertDatabaseHas('auditoria_estados', [
            'entidad_tipo' => EvaluadoOrden::class,
            'entidad_id' => $evaluado->id,
            'campo' => 'estado_evaluacion',
            'estado_anterior' => 'pendiente',
            'estado_nuevo' => 'contactando',
            'user_id' => $admin->id,
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // H-16: Guard impide estados inválidos a nivel de modelo
    // ──────────────────────────────────────────────────────────

    public function test_h16_orden_rechaza_estado_fuera_de_catalogo(): void
    {
        $orden = Orden::factory()->create(['estado' => 'solicitud']);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $orden->estado = 'estado_falso_inventado';
        $orden->save();
    }

    public function test_h16_evaluado_rechaza_estado_evaluacion_invalido(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'estado_evaluacion' => 'pendiente',
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $evaluado->estado_evaluacion = 'basura';
        $evaluado->save();
    }

    // ──────────────────────────────────────────────────────────
    // H-09: PII cifrado en base de datos
    // ──────────────────────────────────────────────────────────

    public function test_h09_observaciones_evaluado_se_cifran_en_bd(): void
    {
        $orden = Orden::factory()->create();
        $plaintext = 'Observacion sensible ' . uniqid();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'observaciones' => $plaintext,
        ]);

        // A través del modelo: descifrado automático
        $this->assertSame($plaintext, $evaluado->fresh()->observaciones);

        // Directamente en BD: debe estar cifrado (no contiene el plaintext)
        $raw = \DB::table('evaluados_orden')->where('id', $evaluado->id)->value('observaciones');
        $this->assertNotSame($plaintext, $raw);
        $this->assertStringStartsWith('eyJ', $raw, 'El valor en BD debe ser un payload de Crypt (base64 JSON).');
    }

    public function test_h09_observaciones_internas_orden_se_cifran_en_bd(): void
    {
        $plaintext = 'Nota interna confidencial ' . uniqid();
        $orden = Orden::factory()->create(['observaciones_internas' => $plaintext]);

        $this->assertSame($plaintext, $orden->fresh()->observaciones_internas);
        $raw = \DB::table('ordenes')->where('id', $orden->id)->value('observaciones_internas');
        $this->assertNotSame($plaintext, $raw);
        $this->assertStringStartsWith('eyJ', $raw);
    }

    // ──────────────────────────────────────────────────────────
    // CO10: Colaboradores (repro role_as=2) no pueden crear usuarios
    // ──────────────────────────────────────────────────────────

    public function test_co10_colaborador_no_puede_acceder_a_crear_usuario(): void
    {
        $repro = $this->crearRepro();

        $response = $this->actingAs($repro)->get(route('users.create'));

        $response->assertForbidden();
    }

    public function test_co10_colaborador_no_puede_enviar_form_crear_usuario(): void
    {
        $repro = $this->crearRepro();

        $response = $this->actingAs($repro)->post(route('users.store'), [
            'name' => 'Test',
            'email' => 'test@test.com',
            'role_as' => 1,
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $response->assertForbidden();
    }

    public function test_co10_admin_puede_acceder_a_crear_usuario(): void
    {
        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)->get(route('users.create'));

        $response->assertOk();
    }

    public function test_co10_colaborador_no_puede_eliminar_usuario(): void
    {
        $repro = $this->crearRepro();
        $otroUsuario = User::factory()->create(['role_as' => 1]);

        $response = $this->actingAs($repro)->delete(route('users.destroy', $otroUsuario->id));

        $response->assertForbidden();
    }

    // R11: repro no puede acceder al listado ni detalle de usuarios
    // ──────────────────────────────────────────────────────────

    public function test_r11_repro_no_puede_listar_usuarios(): void
    {
        $repro = $this->crearRepro();

        $response = $this->actingAs($repro)->get(route('users.index'));

        $response->assertForbidden();
    }

    public function test_r11_repro_no_puede_ver_detalle_usuario(): void
    {
        $repro = $this->crearRepro();
        $otroUsuario = User::factory()->create(['role_as' => 1]);

        $response = $this->actingAs($repro)->get(route('users.show', $otroUsuario->id));

        $response->assertForbidden();
    }

    public function test_r11_empresa_no_puede_listar_usuarios_admin(): void
    {
        $empresa = $this->crearEmpresaUser();

        $response = $this->actingAs($empresa)->get(route('users.index'));

        $response->assertForbidden();
    }

    public function test_r11_admin_puede_listar_usuarios(): void
    {
        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)->get(route('users.index'));

        $response->assertOk();
    }

    public function test_r11_admin_puede_ver_detalle_usuario(): void
    {
        $admin = $this->crearAdmin();
        $otroUsuario = User::factory()->create(['role_as' => 1, 'estado' => 1]);

        $response = $this->actingAs($admin)->get(route('users.show', $otroUsuario->id));

        $response->assertOk();
    }

    // Permisos granulares: rol base desvinculado cuando se asignan permisos individuales
    // ──────────────────────────────────────────────────────────

    public function test_repro_sin_permisos_individuales_no_hereda_permisos_del_rol_base(): void
    {
        $admin = $this->crearAdmin();

        // Crear repro con rol base (que tiene ordenes.eliminar del setUp del crearRepro)
        $repro = User::factory()->create(['role_as' => 2, 'estado' => 1]);
        $reproRole = Role::where('name', 'repro')->first();
        $reproRole->permissions()->syncWithoutDetaching(
            Permission::firstOrCreate(['name' => 'sedes.ver'], ['display_name' => 'Ver sedes', 'module' => 'sedes'])->id
        );
        $repro->roles()->attach($reproRole);

        // Verificar que hereda sedes.ver del rol base
        $this->assertTrue($repro->fresh()->hasPermission('sedes.ver'));

        // Admin guarda permisos individuales vacíos (permisos_enviados sin permisos_sistema)
        $this->actingAs($admin)->put(route('users.update', $repro->id), [
            'name' => $repro->name,
            'email' => $repro->email,
            'role_as' => 2,
            'fecha_nacimiento' => '1990-01-01',
            'permisos_enviados' => '1',
            // permisos_sistema[] ausente = sin permisos
        ]);

        // Ahora el repro NO debe tener sedes.ver (el rol base fue desvinculado)
        $repro->refresh();
        $repro->unsetRelation('roles');
        $this->assertFalse($repro->hasPermission('sedes.ver'));
    }

    public function test_repro_con_permiso_individual_solo_tiene_ese_permiso(): void
    {
        $admin = $this->crearAdmin();

        $repro = User::factory()->create(['role_as' => 2, 'estado' => 1]);
        $reproRole = Role::where('name', 'repro')->first();
        // Dar al rol base sedes.ver y ordenes.ver
        $pSedes = Permission::firstOrCreate(['name' => 'sedes.ver'], ['display_name' => 'Ver sedes', 'module' => 'sedes']);
        $pOrdenes = Permission::firstOrCreate(['name' => 'ordenes.ver'], ['display_name' => 'Ver órdenes', 'module' => 'ordenes']);
        $reproRole->permissions()->syncWithoutDetaching([$pSedes->id, $pOrdenes->id]);
        $repro->roles()->attach($reproRole);

        // Admin guarda solo ordenes.ver como permiso individual
        $this->actingAs($admin)->put(route('users.update', $repro->id), [
            'name' => $repro->name,
            'email' => $repro->email,
            'role_as' => 2,
            'fecha_nacimiento' => '1990-01-01',
            'permisos_enviados' => '1',
            'permisos_sistema' => ['ordenes.ver'],
        ]);

        $repro->refresh();
        $repro->unsetRelation('roles');

        // Tiene ordenes.ver (explícito)
        $this->assertTrue($repro->hasPermission('ordenes.ver'));
        // NO tiene sedes.ver (no fue seleccionado y el rol base fue desvinculado)
        $this->assertFalse($repro->hasPermission('sedes.ver'));
    }
}

