<?php

namespace Tests\Feature;

use App\Models\Cuestionario;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Role;
use App\Models\User;
use App\Support\EmpresaPermisosSupport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

/** Sprint K — WA Stephany 20-ago: PDF cuestionario, alta empresa desde REPRO, Nueva Orden menú. */
class SprintKPortalTest extends TestCase
{
    use RefreshDatabase, CreatesRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
        Mail::fake();
    }

    private function crearAdmin(): User
    {
        $admin = User::factory()->create(['role_as' => 3, 'estado' => 1, 'principal' => 0]);
        $admin->assignRole('admin');

        return $admin;
    }

    private function crearUsuarioEmpresa(Empresa $empresa, array $attrs = []): User
    {
        $user = User::factory()->create(array_merge([
            'role_as' => 1,
            'empresa_id' => $empresa->id,
            'principal' => 1,
            'estado' => 1,
        ], $attrs));
        $user->assignRole('empresa');

        return $user;
    }

    /** @test */
    public function cliente_recibe_403_al_pedir_pdf_cuestionario(): void
    {
        $empresa = Empresa::factory()->create();
        $usuario = $this->crearUsuarioEmpresa($empresa);
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'resultados_visibles_empresa' => true,
        ]);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'cuestionario_completado' => true,
            'texto_informe_preliminar' => '<p>Informe</p>',
        ]);

        $this->actingAs($usuario)
            ->get(route('empresa.cuestionarios.pdf', $evaluado))
            ->assertForbidden();
    }

    /** @test */
    public function estado_de_procesos_cliente_no_muestra_pdf_cuestionario(): void
    {
        $empresa = Empresa::factory()->create();
        $usuario = $this->crearUsuarioEmpresa($empresa);
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'resultados_visibles_empresa' => true,
        ]);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'cuestionario_completado' => true,
            'texto_informe_preliminar' => '<p>Informe</p>',
        ]);
        Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 1,
            'total_secciones' => 1,
            'progreso_porcentaje' => 100,
        ]);

        $response = $this->actingAs($usuario)
            ->get(route('empresa.cuestionarios'));

        $response->assertOk();
        $pdfCuestionario = rtrim(route('empresa.cuestionarios.pdf', $evaluado), '/');
        $this->assertStringNotContainsString($pdfCuestionario.'"', $response->getContent());
        $this->assertStringNotContainsString($pdfCuestionario.'?', $response->getContent());
        $response->assertSee(route('empresa.cuestionarios.pdf-autorizacion', $evaluado), false);
        $response->assertSee(route('empresa.cuestionarios.show', $evaluado), false);
    }

    /** @test */
    public function alta_empresa_desde_repro_sin_principal_queda_como_trabajador(): void
    {
        $admin = $this->crearAdmin();
        $empresa = Empresa::factory()->create(['estado' => 1]);
        $empresaRole = Role::where('name', 'empresa')->firstOrFail();

        $this->actingAs($admin)->post(url('insert-user'), [
            'name' => 'Trabajador REPRO',
            'email' => 'trabajador-repro@example.com',
            'fecha_nacimiento' => '1990-01-01',
            'role_id' => $empresaRole->id,
            'empresa_id' => $empresa->id,
            'cargo' => 'Reclutador',
        ])->assertRedirect('users');

        $trabajador = User::where('email', 'trabajador-repro@example.com')->firstOrFail();
        $this->assertSame(0, (int) $trabajador->principal);
        $this->assertSame(1, (int) $trabajador->role_as);
        $this->assertEqualsCanonicalizing(
            EmpresaPermisosSupport::permisosDefaultTrabajador(),
            $trabajador->permisos
        );
        $this->assertFalse($trabajador->tienePermisoEmpresa('crear_ordenes'));
        $this->assertTrue($trabajador->hasPermission('ordenes.ver'));
        $this->assertFalse($trabajador->hasPermission('usuarios.ver'));

        $this->actingAs($trabajador)
            ->get(route('empresa.usuarios'))
            ->assertRedirect();
    }

    /** @test */
    public function alta_empresa_desde_repro_con_principal_puede_gestionar_usuarios(): void
    {
        $admin = $this->crearAdmin();
        $empresa = Empresa::factory()->create(['estado' => 1]);
        $empresaRole = Role::where('name', 'empresa')->firstOrFail();

        $this->actingAs($admin)->post(url('insert-user'), [
            'name' => 'Titular REPRO',
            'email' => 'titular-repro@example.com',
            'fecha_nacimiento' => '1985-01-01',
            'role_id' => $empresaRole->id,
            'empresa_id' => $empresa->id,
            'principal' => '1',
        ])->assertRedirect('users');

        $titular = User::where('email', 'titular-repro@example.com')->firstOrFail();
        $this->assertSame(1, (int) $titular->principal);

        $this->actingAs($titular)
            ->get(route('empresa.usuarios'))
            ->assertOk();
    }

    /** @test */
    public function menu_repro_incluye_nueva_orden(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->get(url('users'))
            ->assertOk()
            ->assertSee('Nueva Orden')
            ->assertSee(route('ordenes.create'), false);
    }
}
