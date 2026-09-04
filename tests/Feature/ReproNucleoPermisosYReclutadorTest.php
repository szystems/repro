<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Role;
use App\Models\User;
use App\Support\ReproPermisosSupport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

class ReproNucleoPermisosYReclutadorTest extends TestCase
{
    use RefreshDatabase, CreatesRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
    }

    private function empleadoSoloRolPersonal(): User
    {
        $empleado = User::factory()->create(['role_as' => 2, 'estado' => 1]);
        $rolPersonal = Role::create([
            'name' => 'user_'.$empleado->id,
            'display_name' => 'Permisos de '.$empleado->name,
            'level' => 2,
        ]);
        $empleado->roles()->attach($rolPersonal->id);

        return $empleado;
    }

    public function test_empleado_sin_rol_repro_sigue_pudiendo_editar_orden(): void
    {
        $empleado = $this->empleadoSoloRolPersonal();

        $this->assertFalse($empleado->hasRole('repro'));
        $this->assertTrue($empleado->hasPermission('ordenes.editar'));
        $this->assertTrue(ReproPermisosSupport::esNucleo('ordenes.editar'));

        $orden = Orden::factory()->create(['estado' => 'en_proceso']);

        $this->actingAs($empleado)
            ->get(route('ordenes.edit', $orden))
            ->assertOk();
    }

    public function test_empresa_no_tiene_permiso_eliminar_informes(): void
    {
        $empresa = Empresa::factory()->create(['estado' => 1]);
        $gerente = User::factory()->create([
            'role_as' => 1,
            'principal' => 1,
            'estado' => 1,
            'empresa_id' => $empresa->id,
        ]);
        $gerente->assignRole('empresa');

        $this->assertFalse($gerente->hasPermission('resultados.eliminar'));
    }

    public function test_repro_puede_eliminar_informe_final_si_orden_no_entregada(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('resultados/1/informe.pdf', 'pdf');

        $empleado = $this->empleadoSoloRolPersonal();
        $orden = Orden::factory()->create(['estado' => 'en_proceso']);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'archivo_resultado_final' => 'resultados/1/informe.pdf',
        ]);

        $this->actingAs($empleado)
            ->delete(route('evaluados.eliminar-resultado-archivo', [$evaluado->id, 'final']))
            ->assertRedirect();

        $this->assertNull($evaluado->fresh()->archivo_resultado_final);
    }

    public function test_empresa_no_puede_eliminar_informe(): void
    {
        $empresa = Empresa::factory()->create(['estado' => 1]);
        $gerente = User::factory()->create([
            'role_as' => 1,
            'principal' => 1,
            'estado' => 1,
            'empresa_id' => $empresa->id,
        ]);
        $gerente->assignRole('empresa');

        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'estado' => 'en_proceso',
        ]);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'archivo_resultado_final' => 'resultados/1/informe.pdf',
        ]);

        $this->actingAs($gerente)
            ->delete(route('evaluados.eliminar-resultado-archivo', [$evaluado->id, 'final']))
            ->assertForbidden();

        $this->assertSame('resultados/1/informe.pdf', $evaluado->fresh()->archivo_resultado_final);
    }

    public function test_create_muestra_combo_reclutador_para_repro(): void
    {
        $empleado = $this->empleadoSoloRolPersonal();

        $this->actingAs($empleado)
            ->get(route('ordenes.create'))
            ->assertOk()
            ->assertSee('Reclutador asignado')
            ->assertSee('name="reclutador_id"', false);
    }

    public function test_ajax_reclutadores_incluye_gerente_y_trabajadores(): void
    {
        $empleado = $this->empleadoSoloRolPersonal();
        $empresa = Empresa::factory()->create(['estado' => 1]);
        $empresaRole = Role::where('name', 'empresa')->firstOrFail();

        $gerente = User::factory()->create([
            'role_as' => 1,
            'principal' => 1,
            'estado' => 1,
            'empresa_id' => $empresa->id,
            'name' => 'Gerente RRHH Test',
        ]);
        $gerente->roles()->sync([$empresaRole->id]);

        $trabajador = User::factory()->create([
            'role_as' => 1,
            'principal' => 0,
            'estado' => 1,
            'empresa_id' => $empresa->id,
            'name' => 'Trabajador Test',
        ]);
        $trabajador->roles()->sync([$empresaRole->id]);

        $this->actingAs($empleado)
            ->getJson(route('ordenes.reclutadores', ['empresa_id' => $empresa->id]))
            ->assertOk()
            ->assertJsonFragment(['id' => $gerente->id, 'principal' => true])
            ->assertJsonFragment(['id' => $trabajador->id, 'principal' => false]);
    }

    public function test_empresa_no_usa_ajax_reclutadores(): void
    {
        $empresa = Empresa::factory()->create(['estado' => 1]);
        $gerente = User::factory()->create([
            'role_as' => 1,
            'principal' => 1,
            'estado' => 1,
            'empresa_id' => $empresa->id,
        ]);
        $gerente->assignRole('empresa');

        $this->actingAs($gerente)
            ->getJson(route('ordenes.reclutadores', ['empresa_id' => $empresa->id]))
            ->assertForbidden();
    }
}
