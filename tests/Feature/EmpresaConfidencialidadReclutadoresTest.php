<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Orden;
use App\Models\Role;
use App\Models\User;
use App\Support\EmpresaPermisosSupport;
use App\Support\EmpresaVisibilidadReclutadoresSupport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

class EmpresaConfidencialidadReclutadoresTest extends TestCase
{
    use RefreshDatabase, CreatesRolesAndPermissions;

    private Empresa $empresa;

    private User $principal;

    private User $reclutadorA;

    private User $reclutadorB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();

        $empresaRole = Role::where('name', 'empresa')->firstOrFail();

        $this->empresa = Empresa::factory()->create([
            'modo_visibilidad_reclutadores' => EmpresaVisibilidadReclutadoresSupport::MODO_COMPARTIDO,
        ]);

        $this->principal = User::factory()->create([
            'role_as' => 1,
            'empresa_id' => $this->empresa->id,
            'principal' => 1,
            'estado' => 1,
        ]);
        $this->principal->roles()->sync([$empresaRole->id]);

        $permisosTrabajador = json_encode(EmpresaPermisosSupport::permisosDefaultTrabajador());

        $this->reclutadorA = User::factory()->create([
            'role_as' => 1,
            'empresa_id' => $this->empresa->id,
            'principal' => 0,
            'estado' => 1,
            'name' => 'Reclutador A',
            'permisos' => $permisosTrabajador,
        ]);
        $this->reclutadorA->roles()->sync([$empresaRole->id]);

        $this->reclutadorB = User::factory()->create([
            'role_as' => 1,
            'empresa_id' => $this->empresa->id,
            'principal' => 0,
            'estado' => 1,
            'name' => 'Reclutador B',
            'permisos' => $permisosTrabajador,
        ]);
        $this->reclutadorB->roles()->sync([$empresaRole->id]);
    }

    public function test_modo_compartido_trabajador_ve_orden_no_confidencial(): void
    {
        $orden = Orden::factory()->create([
            'empresa_id' => $this->empresa->id,
            'creado_por' => $this->reclutadorA->id,
            'confidencial' => false,
        ]);

        $this->assertTrue(
            EmpresaVisibilidadReclutadoresSupport::puedeVerOrden($this->reclutadorB, $orden)
        );
    }

    public function test_orden_confidencial_solo_visible_para_asignados_y_principal(): void
    {
        $orden = Orden::factory()->create([
            'empresa_id' => $this->empresa->id,
            'creado_por' => $this->reclutadorA->id,
            'reclutador_id' => $this->reclutadorA->id,
            'confidencial' => true,
        ]);

        $this->assertTrue(EmpresaVisibilidadReclutadoresSupport::puedeVerOrden($this->reclutadorA, $orden));
        $this->assertTrue(EmpresaVisibilidadReclutadoresSupport::puedeVerOrden($this->principal, $orden));
        $this->assertFalse(EmpresaVisibilidadReclutadoresSupport::puedeVerOrden($this->reclutadorB, $orden));
    }

    public function test_modo_solo_propios_oculta_ordenes_de_otros_reclutadores(): void
    {
        $this->empresa->update([
            'modo_visibilidad_reclutadores' => EmpresaVisibilidadReclutadoresSupport::MODO_SOLO_PROPIOS,
        ]);

        $orden = Orden::factory()->create([
            'empresa_id' => $this->empresa->id,
            'creado_por' => $this->reclutadorA->id,
            'confidencial' => false,
        ]);

        $this->assertTrue(EmpresaVisibilidadReclutadoresSupport::puedeVerOrden($this->reclutadorA, $orden));
        $this->assertFalse(EmpresaVisibilidadReclutadoresSupport::puedeVerOrden($this->reclutadorB, $orden));
    }

    public function test_trabajador_no_accede_detalle_orden_confidencial(): void
    {
        $orden = Orden::factory()->create([
            'empresa_id' => $this->empresa->id,
            'creado_por' => $this->reclutadorA->id,
            'reclutador_id' => $this->reclutadorA->id,
            'confidencial' => true,
        ]);

        $this->actingAs($this->reclutadorB)
            ->get(route('ordenes.show', $orden))
            ->assertForbidden();
    }

    public function test_listado_ordenes_filtra_confidenciales_para_trabajador(): void
    {
        $visible = Orden::factory()->create([
            'empresa_id' => $this->empresa->id,
            'creado_por' => $this->reclutadorA->id,
            'confidencial' => false,
            'codigo_orden' => 'ORD-2026-9001',
        ]);

        $oculta = Orden::factory()->create([
            'empresa_id' => $this->empresa->id,
            'creado_por' => $this->reclutadorA->id,
            'reclutador_id' => $this->reclutadorA->id,
            'confidencial' => true,
            'codigo_orden' => 'ORD-2026-9002',
        ]);

        $response = $this->actingAs($this->reclutadorB)->get(route('ordenes.index'));

        $response->assertOk()
            ->assertSee($visible->codigo_orden)
            ->assertDontSee($oculta->codigo_orden);
    }

    public function test_filtro_listado_funciona_cuando_role_as_es_string(): void
    {
        $this->reclutadorB->forceFill(['role_as' => '1'])->save();

        $visible = Orden::factory()->create([
            'empresa_id' => $this->empresa->id,
            'creado_por' => $this->reclutadorA->id,
            'confidencial' => false,
            'codigo_orden' => 'ORD-2026-9011',
        ]);

        $oculta = Orden::factory()->create([
            'empresa_id' => $this->empresa->id,
            'creado_por' => $this->reclutadorA->id,
            'reclutador_id' => $this->reclutadorA->id,
            'confidencial' => true,
            'codigo_orden' => 'ORD-2026-9012',
        ]);

        $query = Orden::query();
        EmpresaVisibilidadReclutadoresSupport::filtrarQueryOrdenesEmpresa($query, $this->reclutadorB->fresh());

        $codigos = $query->pluck('codigo_orden')->all();

        $this->assertContains($visible->codigo_orden, $codigos);
        $this->assertNotContains($oculta->codigo_orden, $codigos);
    }

    public function test_formulario_nueva_orden_muestra_confidencial_al_reclutador(): void
    {
        $this->habilitarCrearOrdenes($this->reclutadorA);

        $this->actingAs($this->reclutadorA->fresh())
            ->get(route('ordenes.create'))
            ->assertOk()
            ->assertSee('Proceso')
            ->assertSee('confidencial');
    }

    public function test_reclutador_puede_crear_orden_confidencial(): void
    {
        $this->habilitarCrearOrdenes($this->reclutadorA);

        $this->actingAs($this->reclutadorA->fresh())
            ->post(route('ordenes.store'), [
                'empresa_id' => $this->empresa->id,
                'evaluados' => [[
                    'nombre' => 'Carla',
                    'apellidos' => 'Méndez',
                    'dpi' => '1234567890123',
                    'email' => 'carla.conf@test.com',
                    'tipo_servicio' => 'poligrafo',
                    'tipo_formulario' => 'preempleo',
                ]],
                'confidencial' => '1',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $orden = Orden::where('creado_por', $this->reclutadorA->id)->latest('id')->first();
        $this->assertNotNull($orden);
        $this->assertTrue((bool) $orden->confidencial);
        $this->assertSame($this->reclutadorA->id, $orden->reclutador_id);
        $this->assertTrue(EmpresaVisibilidadReclutadoresSupport::puedeVerOrden($this->principal, $orden));
        $this->assertFalse(EmpresaVisibilidadReclutadoresSupport::puedeVerOrden($this->reclutadorB, $orden));
    }

    private function habilitarCrearOrdenes(User $user): void
    {
        $permisos = EmpresaPermisosSupport::permisosDefaultTrabajador();
        $permisos[] = 'crear_ordenes';
        $user->update(['permisos' => json_encode($permisos)]);
    }
}
