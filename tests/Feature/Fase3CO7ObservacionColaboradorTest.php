<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase3CO7ObservacionColaboradorTest extends TestCase
{
    use RefreshDatabase;

    protected User $colaborador;
    protected User $empresaUser;
    protected Empresa $empresa;
    protected Orden $orden;
    protected EvaluadoOrden $evaluado;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'admin', 'display_name' => 'Administrador']);
        Role::create(['name' => 'empresa', 'display_name' => 'Empresa']);
        Role::create(['name' => 'repro', 'display_name' => 'REPRO']);

        $this->empresa = Empresa::factory()->create();

        $this->colaborador = User::factory()->create(['role_as' => 2, 'estado' => 1]);
        $this->colaborador->roles()->attach(Role::where('name', 'repro')->first());

        $this->empresaUser = User::factory()->create([
            'role_as' => 1,
            'estado' => 1,
            'empresa_id' => $this->empresa->id,
        ]);
        $this->empresaUser->roles()->attach(Role::where('name', 'empresa')->first());

        $this->orden = Orden::factory()->create([
            'empresa_id' => $this->empresa->id,
            'creado_por' => $this->colaborador->id,
        ]);

        $this->evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
        ]);
    }

    public function test_colaborador_puede_guardar_observacion(): void
    {
        $response = $this->actingAs($this->colaborador)
            ->patch(route('evaluados.actualizar-observacion', $this->evaluado), [
                'observaciones' => 'Candidato llegó puntual, sin inconvenientes.',
            ]);

        $response->assertRedirect();
        $this->evaluado->refresh();
        $this->assertEquals('Candidato llegó puntual, sin inconvenientes.', $this->evaluado->observaciones);
    }

    public function test_observacion_es_visible_para_empresa_en_show(): void
    {
        $this->evaluado->update(['observaciones' => 'Nota para la empresa.']);

        $response = $this->actingAs($this->empresaUser)
            ->get(route('ordenes.show', $this->orden));

        $response->assertStatus(200);
        $response->assertSee('Nota para la empresa.');
    }

    public function test_empresa_no_puede_editar_observacion(): void
    {
        $response = $this->actingAs($this->empresaUser)
            ->patch(route('evaluados.actualizar-observacion', $this->evaluado), [
                'observaciones' => 'Intento de empresa.',
            ]);

        $response->assertRedirect();
        $this->evaluado->refresh();
        $this->assertNotEquals('Intento de empresa.', $this->evaluado->observaciones);
    }

    public function test_colaborador_puede_borrar_observacion(): void
    {
        $this->evaluado->update(['observaciones' => 'Observación existente.']);

        $response = $this->actingAs($this->colaborador)
            ->patch(route('evaluados.actualizar-observacion', $this->evaluado), [
                'observaciones' => '',
            ]);

        $response->assertRedirect();
        $this->evaluado->refresh();
        $this->assertEmpty($this->evaluado->observaciones);
    }
}
