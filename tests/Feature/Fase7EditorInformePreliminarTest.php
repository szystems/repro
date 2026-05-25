<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

/**
 * Tests para Fase 7: CO6 — Editor de texto enriquecido para informe preliminar.
 */
class Fase7EditorInformePreliminarTest extends TestCase
{
    use RefreshDatabase, CreatesRolesAndPermissions;

    protected User $admin;
    protected User $repro;
    protected User $empresaUser;
    protected Empresa $empresa;
    protected Orden $orden;
    protected EvaluadoOrden $evaluado;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpRolesAndPermissions();

        $this->admin       = User::factory()->create(['role_as' => 3]);
        $this->repro       = User::factory()->create(['role_as' => 2]);
        $this->repro->roles()->attach(Role::where('name', 'repro')->first());
        $this->empresa     = Empresa::factory()->create(['estado' => 1]);
        $this->empresaUser = User::factory()->create(['role_as' => 1, 'empresa_id' => $this->empresa->id]);
        $this->empresaUser->roles()->attach(Role::where('name', 'empresa')->first());

        $this->orden = Orden::factory()->create([
            'empresa_id' => $this->empresa->id,
            'estado'     => 'preliminar',
        ]);

        $this->evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
        ]);
    }

    /** @test */
    public function co6_admin_puede_guardar_informe_preliminar(): void
    {
        $texto = '<p>Este es el <strong>informe preliminar</strong> del evaluado.</p>';

        $response = $this->actingAs($this->admin)
            ->patch(route('evaluados.guardar-informe-preliminar', $this->evaluado->id), [
                'texto_informe_preliminar' => $texto,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('evaluados_orden', [
            'id'                       => $this->evaluado->id,
            'texto_informe_preliminar' => $texto,
        ]);
    }

    /** @test */
    public function co6_repro_puede_guardar_informe_preliminar(): void
    {
        $texto = '<p>Informe redactado por repro.</p>';

        $response = $this->actingAs($this->repro)
            ->patch(route('evaluados.guardar-informe-preliminar', $this->evaluado->id), [
                'texto_informe_preliminar' => $texto,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('evaluados_orden', [
            'id'                       => $this->evaluado->id,
            'texto_informe_preliminar' => $texto,
        ]);
    }

    /** @test */
    public function co6_empresa_no_puede_guardar_informe_preliminar(): void
    {
        $response = $this->actingAs($this->empresaUser)
            ->patch(route('evaluados.guardar-informe-preliminar', $this->evaluado->id), [
                'texto_informe_preliminar' => '<p>Intento empresa.</p>',
            ]);

        // El middleware bloquea con 403 (empresa no tiene permiso informe_preliminar.editar)
        $response->assertForbidden();

        $this->assertDatabaseMissing('evaluados_orden', [
            'id'                       => $this->evaluado->id,
            'texto_informe_preliminar' => '<p>Intento empresa.</p>',
        ]);
    }

    /** @test */
    public function co6_informe_puede_guardarse_vacio(): void
    {
        $this->evaluado->update(['texto_informe_preliminar' => '<p>Texto previo</p>']);

        $response = $this->actingAs($this->admin)
            ->patch(route('evaluados.guardar-informe-preliminar', $this->evaluado->id), [
                'texto_informe_preliminar' => null,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('evaluados_orden', [
            'id'                       => $this->evaluado->id,
            'texto_informe_preliminar' => null,
        ]);
    }

    /** @test */
    public function co6_ruta_requiere_autenticacion(): void
    {
        $response = $this->patch(route('evaluados.guardar-informe-preliminar', $this->evaluado->id), [
            'texto_informe_preliminar' => '<p>Test</p>',
        ]);

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function co6_show_orden_muestra_editor_para_admin(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('ordenes.show', $this->orden->id));

        $response->assertStatus(200);
        $response->assertSee('Informe Preliminar');
        $response->assertSee('editor-preliminar-' . $this->evaluado->id);
    }
}
