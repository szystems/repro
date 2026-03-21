<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Notifications\CuestionarioCompletadoNotification;
use App\Notifications\OrdenCreadaNotification;
use App\Notifications\ResultadosDisponiblesNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class Phase8FFuncionalidadesComplejasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    private function crearAdmin(): User
    {
        return User::factory()->admin()->create();
    }

    private function crearRepro(): User
    {
        return User::factory()->repro()->create();
    }

    private function crearUsuarioEmpresa(?Empresa $empresa = null): User
    {
        $empresa = $empresa ?? Empresa::factory()->create();
        return User::factory()->empresa()->create([
            'empresa_id' => $empresa->id,
            'principal' => 1,
        ]);
    }

    // ========================================
    // 8F.1 — SISTEMA DE NOTIFICACIONES
    // ========================================

    public function test_notificacion_creada_al_crear_orden(): void
    {
        Notification::fake();

        $empresa = Empresa::factory()->create();
        $admin = $this->crearAdmin();
        $repro = $this->crearRepro();

        $this->actingAs($admin)->post(route('ordenes.store'), [
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
            ],
        ]);

        // El repro debe recibir la notificación (admin no, porque es el creador)
        Notification::assertSentTo($repro, OrdenCreadaNotification::class);
        Notification::assertNotSentTo($admin, OrdenCreadaNotification::class);
    }

    public function test_api_notificaciones_devuelve_json(): void
    {
        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)
            ->getJson(route('notificaciones.index'));

        $response->assertOk()
            ->assertJsonStructure(['count', 'notificaciones']);
    }

    public function test_marcar_notificacion_como_leida(): void
    {
        $admin = $this->crearAdmin();
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
        ]);

        $admin->notify(new OrdenCreadaNotification($orden));

        $notificacion = $admin->unreadNotifications()->first();
        $this->assertNotNull($notificacion);

        $response = $this->actingAs($admin)
            ->patchJson(route('notificaciones.leer', $notificacion->id));

        $response->assertOk();
        $this->assertEquals(0, $admin->fresh()->unreadNotifications()->count());
    }

    public function test_marcar_todas_notificaciones_leidas(): void
    {
        $admin = $this->crearAdmin();
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
        ]);

        // Crear varias notificaciones
        $admin->notify(new OrdenCreadaNotification($orden));
        $admin->notify(new OrdenCreadaNotification($orden));

        $this->assertEquals(2, $admin->unreadNotifications()->count());

        $response = $this->actingAs($admin)
            ->postJson(route('notificaciones.leer-todas'));

        $response->assertOk();
        $this->assertEquals(0, $admin->fresh()->unreadNotifications()->count());
    }

    public function test_campana_notificaciones_visible_en_admin_nav(): void
    {
        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)->get(route('ordenes.index'));

        $response->assertOk();
        $response->assertSee('notificacionesBell');
    }

    public function test_campana_notificaciones_visible_en_empresa_nav(): void
    {
        $empresa = Empresa::factory()->create();
        $user = $this->crearUsuarioEmpresa($empresa);

        $response = $this->actingAs($user)->get(route('empresa.ordenes.index'));

        $response->assertOk();
        $response->assertSee('notificacionesBell');
    }

    public function test_notificacion_tiene_datos_correctos(): void
    {
        $admin = $this->crearAdmin();
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
        ]);

        $admin->notify(new OrdenCreadaNotification($orden));

        $notificacion = $admin->notifications()->first();
        $data = $notificacion->data;

        $this->assertEquals('orden_creada', $data['tipo']);
        $this->assertEquals('bi-folder-plus', $data['icono']);
        $this->assertStringContains('Nueva orden', $data['mensaje']);
    }

    // ========================================
    // 8F.2 — PERMISOS GRANULARES REPRO
    // ========================================

    public function test_roles_y_permisos_seeder_ejecutado(): void
    {
        $this->assertDatabaseHas('roles', ['name' => 'admin']);
        $this->assertDatabaseHas('roles', ['name' => 'repro']);
        $this->assertDatabaseHas('roles', ['name' => 'empresa']);
        $this->assertGreaterThanOrEqual(24, Permission::count());
    }

    public function test_admin_siempre_pasa_middleware_permission(): void
    {
        $admin = $this->crearAdmin();

        // Admin accede a reportes (protegido con permission middleware)
        $response = $this->actingAs($admin)->get(route('reportes.evaluaciones'));
        $response->assertOk();
    }

    public function test_repro_con_permiso_accede_reportes(): void
    {
        $repro = $this->crearRepro();

        // Asignar rol repro que tiene reportes.ver
        $role = Role::where('name', 'repro')->first();
        $repro->roles()->syncWithoutDetaching([$role->id]);

        $response = $this->actingAs($repro)->get(route('reportes.evaluaciones'));
        $response->assertOk();
    }

    public function test_user_model_tiene_metodos_permisos(): void
    {
        $admin = $this->crearAdmin();
        $role = Role::where('name', 'admin')->first();
        $admin->roles()->syncWithoutDetaching([$role->id]);

        $this->assertTrue($admin->hasRole('admin'));
        $this->assertTrue($admin->hasPermission('ordenes.ver'));
        $this->assertTrue($admin->hasAnyPermission(['ordenes.ver', 'empresas.ver']));
    }

    public function test_middleware_permission_registrado(): void
    {
        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        $router = app('router');
        $routeMiddleware = $router->getMiddleware();

        $this->assertArrayHasKey('permission', $routeMiddleware);
    }

    public function test_permisos_granulares_en_vista_editar_usuario(): void
    {
        $admin = $this->crearAdmin();
        $repro = $this->crearRepro();

        $response = $this->actingAs($admin)->get(url('edit-user/' . $repro->id));

        $response->assertOk();
        $response->assertSee('Permisos del usuario');
        $response->assertSee('permisos_sistema[]');
    }

    public function test_guardar_permisos_sistema_usuario_repro(): void
    {
        $admin = $this->crearAdmin();
        $repro = $this->crearRepro();

        $response = $this->actingAs($admin)->put(url('update-user/' . $repro->id), [
            'name' => $repro->name,
            'email' => $repro->email,
            'role_as' => 2,
            'cargo' => 'Poligrafista',
            'fecha_nacimiento' => '1990-01-15',
            'permisos_sistema' => ['ordenes.ver', 'evaluaciones.ver', 'reportes.ver'],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        // Verificar que se creó un rol personal con los permisos
        $personalRole = Role::where('name', 'user_' . $repro->id)->first();
        $this->assertNotNull($personalRole);
        $this->assertEquals(3, $personalRole->permissions()->count());
    }

    // ========================================
    // 8F.3 — PERMISOS ADMIN EMPRESA
    // ========================================

    public function test_admin_empresa_ve_permisos_al_crear_usuario(): void
    {
        $empresa = Empresa::factory()->create();
        $admin = $this->crearUsuarioEmpresa($empresa);

        $response = $this->actingAs($admin)->get(route('empresa.usuarios.create'));

        $response->assertOk();
        $response->assertSee('Permisos del usuario');
        $response->assertSee('permisos_empresa[]');
    }

    public function test_permisos_empresa_guardados_al_crear_sub_usuario(): void
    {
        $empresa = Empresa::factory()->create();
        $admin = $this->crearUsuarioEmpresa($empresa);

        $this->actingAs($admin)->post(route('empresa.usuarios.store'), [
            'name' => 'Sub Usuario',
            'email' => 'sub@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'permisos_empresa' => ['ver_ordenes', 'ver_resultados'],
        ]);

        $subUser = User::where('email', 'sub@test.com')->first();
        $this->assertNotNull($subUser);
        $permisos = json_decode($subUser->permisos, true);
        $this->assertContains('ver_ordenes', $permisos);
        $this->assertContains('ver_resultados', $permisos);
        $this->assertNotContains('crear_ordenes', $permisos);
    }

    public function test_permisos_empresa_actualizados_al_editar_sub_usuario(): void
    {
        $empresa = Empresa::factory()->create();
        $admin = $this->crearUsuarioEmpresa($empresa);
        $subUser = User::factory()->empresa()->create([
            'empresa_id' => $empresa->id,
            'principal' => 0,
            'permisos' => json_encode(['ver_ordenes']),
        ]);

        $this->actingAs($admin)->put(route('empresa.usuarios.update', $subUser), [
            'name' => $subUser->name,
            'email' => $subUser->email,
            'estado' => 1,
            'permisos_empresa' => ['ver_ordenes', 'crear_ordenes', 'ver_reportes'],
        ]);

        $permisos = json_decode($subUser->fresh()->permisos, true);
        $this->assertCount(3, $permisos);
        $this->assertContains('ver_reportes', $permisos);
    }

    public function test_usuario_principal_siempre_tiene_permisos(): void
    {
        $empresa = Empresa::factory()->create();
        $admin = $this->crearUsuarioEmpresa($empresa);

        $this->assertTrue($admin->tienePermisoEmpresa('ver_ordenes'));
        $this->assertTrue($admin->tienePermisoEmpresa('crear_ordenes'));
        $this->assertTrue($admin->tienePermisoEmpresa('cualquier_permiso'));
    }

    public function test_sub_usuario_solo_tiene_permisos_asignados(): void
    {
        $empresa = Empresa::factory()->create();
        $subUser = User::factory()->empresa()->create([
            'empresa_id' => $empresa->id,
            'principal' => 0,
            'permisos' => json_encode(['ver_ordenes', 'ver_resultados']),
        ]);

        $this->assertTrue($subUser->tienePermisoEmpresa('ver_ordenes'));
        $this->assertTrue($subUser->tienePermisoEmpresa('ver_resultados'));
        $this->assertFalse($subUser->tienePermisoEmpresa('crear_ordenes'));
        $this->assertFalse($subUser->tienePermisoEmpresa('ver_reportes'));
    }

    // ========================================
    // HELPERS
    // ========================================

    private function assertStringContains(string $needle, string $haystack): void
    {
        $this->assertTrue(
            str_contains($haystack, $needle),
            "Failed asserting that '$haystack' contains '$needle'"
        );
    }
}
