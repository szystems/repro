<?php

namespace Tests\Feature;

use App\Mail\ComentarioAgregadoMail;
use App\Models\Empresa;
use App\Models\EvaluadoObservacionEntrada;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

class ComentarioCorreoEmpresaTest extends TestCase
{
    use RefreshDatabase, CreatesRolesAndPermissions;

    private User $repro;

    private User $titular;

    private Empresa $empresa;

    private EvaluadoOrden $evaluado;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
        Mail::fake();

        $this->empresa = Empresa::factory()->create([
            'nombre' => 'Cliente Comentarios',
            'correos_automaticos' => true,
            'correos_comentarios' => false,
        ]);

        $this->repro = User::factory()->create(['role_as' => 2, 'estado' => 1]);
        $this->repro->roles()->attach(Role::where('name', 'repro')->first());

        $rolEmpresa = Role::where('name', 'empresa')->first();
        $this->titular = User::factory()->create([
            'name' => 'Titular RRHH',
            'email' => 'titular.comentarios@cliente.test',
            'role_as' => 1,
            'empresa_id' => $this->empresa->id,
            'principal' => 1,
            'estado' => 1,
        ]);
        $this->titular->roles()->attach($rolEmpresa);

        $orden = Orden::factory()->create([
            'empresa_id' => $this->empresa->id,
            'tipo_creador' => 'repro',
            'creado_por' => $this->repro->id,
            'reclutador_id' => null,
        ]);
        $this->evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'nombre' => 'Ana',
            'apellidos' => 'López',
            'observaciones' => null,
        ]);
    }

    public function test_un_comentario_nuevo_no_manda_correo_si_el_aviso_esta_apagado(): void
    {
        $this->actingAs($this->repro)
            ->patch(route('evaluados.actualizar-observacion', $this->evaluado), [
                'observaciones' => 'Se le llamó y no contestó.',
            ])
            ->assertRedirect();

        Mail::assertNotQueued(ComentarioAgregadoMail::class);
    }

    public function test_con_el_aviso_encendido_el_comentario_llega_al_titular(): void
    {
        $this->empresa->update(['correos_comentarios' => true]);

        $this->actingAs($this->repro)
            ->patch(route('evaluados.actualizar-observacion', $this->evaluado), [
                'observaciones' => 'Confirmó la cita de mañana.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        Mail::assertQueued(ComentarioAgregadoMail::class, function (ComentarioAgregadoMail $mail) {
            return $mail->hasTo('titular.comentarios@cliente.test')
                && $mail->comentario === 'Confirmó la cita de mañana.'
                && str_contains($mail->envelope()->subject, 'Ana López');
        });
    }

    public function test_si_los_correos_generales_estan_apagados_no_sale_el_de_comentarios(): void
    {
        $this->empresa->update([
            'correos_automaticos' => false,
            'correos_comentarios' => true,
        ]);

        $this->actingAs($this->repro)
            ->patch(route('evaluados.actualizar-observacion', $this->evaluado), [
                'observaciones' => 'Nota que no debe salir por correo.',
            ])
            ->assertRedirect();

        Mail::assertNotQueued(ComentarioAgregadoMail::class);
    }

    public function test_corregir_un_comentario_no_manda_otro_correo(): void
    {
        $this->empresa->update(['correos_comentarios' => true]);
        $this->evaluado->agregarObservacion('Texto con un error.', $this->repro->id);
        $nota = EvaluadoObservacionEntrada::where('evaluado_orden_id', $this->evaluado->id)->first();
        Mail::fake();

        $this->actingAs($this->repro)
            ->patch(route('evaluados.corregir-observacion', [$this->evaluado, $nota]), [
                'observaciones' => 'Texto corregido.',
            ])
            ->assertRedirect();

        Mail::assertNotQueued(ComentarioAgregadoMail::class);
    }

    public function test_el_interruptor_de_comentarios_nace_apagado_y_se_enciende_aparte(): void
    {
        $admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        $this->assertFalse($this->empresa->recibeCorreosComentarios());

        $this->actingAs($admin)
            ->patch(route('empresas.correos-automaticos', $this->empresa->id), [
                'correos_comentarios' => '1',
            ])
            ->assertRedirect();

        $this->empresa->refresh();
        $this->assertTrue($this->empresa->recibeCorreosAutomaticos());
        $this->assertTrue($this->empresa->recibeCorreosComentarios());
    }
}
