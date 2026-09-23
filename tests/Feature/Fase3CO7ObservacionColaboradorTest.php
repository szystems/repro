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

class Fase3CO7ObservacionColaboradorTest extends TestCase
{
    use RefreshDatabase, CreatesRolesAndPermissions;

    protected User $colaborador;
    protected User $empresaUser;
    protected Empresa $empresa;
    protected Orden $orden;
    protected EvaluadoOrden $evaluado;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();

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
            ->get(route('empresa.ordenes.show', $this->orden));

        $response->assertStatus(200);
        $response->assertSee('Nota para la empresa.');
    }

    public function test_empresa_no_puede_editar_observacion(): void
    {
        $response = $this->actingAs($this->empresaUser)
            ->patch(route('evaluados.actualizar-observacion', $this->evaluado), [
                'observaciones' => 'Intento de empresa.',
            ]);

        $response->assertForbidden();
        $this->evaluado->refresh();
        $this->assertNotEquals('Intento de empresa.', $this->evaluado->observaciones);
    }

    public function test_guardar_vacio_no_borra_la_observacion(): void
    {
        $this->evaluado->update(['observaciones' => 'Observación existente.']);

        $response = $this->actingAs($this->colaborador)
            ->patch(route('evaluados.actualizar-observacion', $this->evaluado), [
                'observaciones' => '',
            ]);

        $response->assertRedirect();
        $this->evaluado->refresh();
        $this->assertSame('Observación existente.', $this->evaluado->observaciones);
    }

    public function test_un_comentario_nuevo_conserva_el_anterior_con_fecha(): void
    {
        $this->evaluado->update(['observaciones' => 'Observación existente.']);

        $response = $this->actingAs($this->colaborador)
            ->patch(route('evaluados.actualizar-observacion', $this->evaluado), [
                'observaciones' => 'Segundo comentario, el candidato confirmó la cita.',
            ]);

        $response->assertRedirect();
        $this->evaluado->refresh();
        $this->assertSame('Segundo comentario, el candidato confirmó la cita.', $this->evaluado->observaciones);
        $this->assertSame(2, $this->evaluado->entradasObservacion()->count());

        $ficha = $this->actingAs($this->colaborador)->get(route('ordenes.show', $this->orden));
        $ficha->assertOk();
        $ficha->assertSee('Observación existente.');
        $ficha->assertSee('Segundo comentario, el candidato confirmó la cita.');
        $ficha->assertSee(now()->timezone('America/Guatemala')->format('d/m/Y H:i'));
        $ficha->assertSee($this->colaborador->name);

        $empresa = $this->actingAs($this->empresaUser)->get(route('empresa.ordenes.show', $this->orden));
        $empresa->assertOk();
        $empresa->assertSee('Observación existente.');
        $empresa->assertSee('Segundo comentario, el candidato confirmó la cita.');
        $empresa->assertDontSee('Agregar observación');
        $empresa->assertDontSee('Guardar corrección');
    }

    public function test_repro_puede_corregir_una_nota_sin_borrar_las_demas(): void
    {
        $this->evaluado->update(['observaciones' => null]);
        $this->evaluado->agregarObservacion('Se le intentó llamar, tiró a buzón.', $this->colaborador->id);
        $this->evaluado->agregarObservacion('Indicó que hoy enviará la información.', $this->colaborador->id);
        $nota = $this->evaluado->entradasObservacion()->reorder()->orderBy('id')->first();
        $fecha = $nota->created_at->format('Y-m-d H:i:s');

        $this->actingAs($this->colaborador)
            ->patch(route('evaluados.corregir-observacion', [$this->evaluado, $nota]), [
                'observaciones' => 'Se le intentó llamar, no contestó.',
            ])
            ->assertRedirect();

        $nota->refresh();
        $this->evaluado->refresh();
        $this->assertSame('Se le intentó llamar, no contestó.', $nota->texto);
        $this->assertSame($fecha, $nota->created_at->format('Y-m-d H:i:s'));
        $this->assertSame($this->colaborador->id, $nota->user_id);
        $this->assertSame(2, $this->evaluado->entradasObservacion()->count());
        $this->assertSame('Indicó que hoy enviará la información.', $this->evaluado->observaciones);

        $ficha = $this->actingAs($this->colaborador)->get(route('ordenes.show', $this->orden));
        $ficha->assertOk();
        $ficha->assertSee('Se le intentó llamar, no contestó.');
        $ficha->assertSee('Indicó que hoy enviará la información.');
        $ficha->assertSee('Guardar corrección');
        $ficha->assertDontSee('Se le intentó llamar, tiró a buzón.');
    }

    public function test_corregir_la_nota_mas_reciente_actualiza_el_texto_vigente(): void
    {
        $this->evaluado->update(['observaciones' => null]);
        $this->evaluado->agregarObservacion('Nota vieja.', $this->colaborador->id);
        $this->evaluado->agregarObservacion('Nota con un error.', $this->colaborador->id);
        $ultima = $this->evaluado->entradasObservacion()->first();

        $this->actingAs($this->colaborador)
            ->patch(route('evaluados.corregir-observacion', [$this->evaluado, $ultima]), [
                'observaciones' => 'Nota corregida.',
            ])
            ->assertRedirect();

        $this->assertSame('Nota corregida.', $this->evaluado->fresh()->observaciones);
        $this->assertSame('Nota vieja.', $this->evaluado->entradasObservacion()->reorder()->orderBy('id')->first()->texto);
    }

    public function test_corregir_en_blanco_no_borra_la_nota(): void
    {
        $this->evaluado->agregarObservacion('Texto que debe quedarse.', $this->colaborador->id);
        $nota = $this->evaluado->entradasObservacion()->first();

        $this->actingAs($this->colaborador)
            ->from(route('ordenes.show', $this->orden))
            ->patch(route('evaluados.corregir-observacion', [$this->evaluado, $nota]), [
                'observaciones' => '   ',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('observaciones');

        $this->assertSame('Texto que debe quedarse.', $nota->fresh()->texto);
    }

    public function test_empresa_no_puede_corregir_una_observacion(): void
    {
        $this->evaluado->agregarObservacion('Nota para la empresa.', $this->colaborador->id);
        $nota = $this->evaluado->entradasObservacion()->first();

        $this->actingAs($this->empresaUser)
            ->patch(route('evaluados.corregir-observacion', [$this->evaluado, $nota]), [
                'observaciones' => 'Texto cambiado por la empresa.',
            ])
            ->assertForbidden();

        $this->assertSame('Nota para la empresa.', $nota->fresh()->texto);
    }

    public function test_no_se_puede_corregir_la_nota_de_otro_candidato(): void
    {
        $otro = EvaluadoOrden::factory()->create(['orden_id' => $this->orden->id]);
        $otro->agregarObservacion('Nota del otro candidato.', $this->colaborador->id);
        $notaAjena = $otro->entradasObservacion()->first();

        $this->actingAs($this->colaborador)
            ->patch(route('evaluados.corregir-observacion', [$this->evaluado, $notaAjena]), [
                'observaciones' => 'No debe aplicarse.',
            ])
            ->assertNotFound();

        $this->assertSame('Nota del otro candidato.', $notaAjena->fresh()->texto);
    }

    public function test_se_puede_corregir_la_observacion_vieja_de_una_sola_casilla(): void
    {
        $this->evaluado->update(['observaciones' => 'Texto con un error.']);

        $this->actingAs($this->colaborador)
            ->patch(route('evaluados.corregir-observacion-inicial', $this->evaluado), [
                'observaciones' => 'Texto corregido.',
            ])
            ->assertRedirect();

        $this->evaluado->refresh();
        $this->assertSame('Texto corregido.', $this->evaluado->observaciones);
        $this->assertSame(0, $this->evaluado->entradasObservacion()->count());
    }
}
