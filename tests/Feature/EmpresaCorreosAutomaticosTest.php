<?php

namespace Tests\Feature;

use App\Mail\CitaProgramadaMail;
use App\Mail\EvaluadoAsignadoMail;
use App\Mail\RecordatorioCuestionarioMail;
use App\Mail\ResultadosDisponiblesMail;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Role;
use App\Models\Sede;
use App\Models\User;
use App\Notifications\ResultadosDisponiblesNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

class EmpresaCorreosAutomaticosTest extends TestCase
{
    use RefreshDatabase, CreatesRolesAndPermissions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
        Mail::fake();
        Notification::fake();

        $this->admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);
        $this->admin->roles()->attach(Role::where('name', 'admin')->first());
    }

    public function test_el_interruptor_del_listado_apaga_y_enciende_los_correos(): void
    {
        $empresa = Empresa::factory()->create([
            'nombre' => 'Cliente Con Correo',
            'estado' => 1,
            'correos_automaticos' => true,
        ]);

        $this->actingAs($this->admin)
            ->get(route('empresas.index'))
            ->assertOk()
            ->assertSee('Correos automáticos de Cliente Con Correo', false);

        $this->actingAs($this->admin)
            ->patch(route('empresas.correos-automaticos', $empresa->id), [
                'correos_automaticos' => '0',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertFalse($empresa->fresh()->recibeCorreosAutomaticos());

        $this->actingAs($this->admin)
            ->patch(route('empresas.correos-automaticos', $empresa->id), [
                'correos_automaticos' => '1',
            ])
            ->assertRedirect();

        $this->assertTrue($empresa->fresh()->recibeCorreosAutomaticos());
    }

    public function test_la_ficha_puede_apagar_los_correos(): void
    {
        $empresa = Empresa::factory()->create([
            'nombre' => 'Cliente Ficha',
            'correos_automaticos' => true,
        ]);

        $this->actingAs($this->admin)
            ->put(route('empresas.update', $empresa->id), [
                'nombre' => 'Cliente Ficha',
                'correos_automaticos' => '0',
            ])
            ->assertRedirect();

        $this->assertFalse($empresa->fresh()->recibeCorreosAutomaticos());
    }

    public function test_empresa_apagada_no_recibe_correo_de_resultados_y_si_la_campana(): void
    {
        $empresa = Empresa::factory()->create(['correos_automaticos' => false]);
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'resultados_visibles_empresa' => false,
            'creado_por' => $this->admin->id,
        ]);
        EvaluadoOrden::factory()->create(['orden_id' => $orden->id]);

        $this->actingAs($this->admin)
            ->patch(route('ordenes.toggle-resultados-visibles', $orden))
            ->assertRedirect()
            ->assertSessionHas('success');

        Mail::assertNotQueued(ResultadosDisponiblesMail::class);
        Notification::assertSentTo($this->admin, ResultadosDisponiblesNotification::class);
    }

    public function test_empresa_apagada_no_manda_el_enlace_al_candidato(): void
    {
        $empresa = Empresa::factory()->create(['estado' => 1, 'correos_automaticos' => false]);

        $this->actingAs($this->admin)->post(route('ordenes.store'), [
            'empresa_id' => $empresa->id,
            'observaciones_internas' => 'Sin correo',
            'prioridad' => 'normal',
            'evaluados' => [
                [
                    'nombre' => 'Test',
                    'apellidos' => 'Sin Correo',
                    'dpi' => '1234509870123',
                    'email' => 'sin.correo@evaluado.test',
                    'telefono' => '55551234',
                    'tipo_servicio' => 'poligrafo',
                    'tipo_formulario' => 'preempleo',
                ],
            ],
        ])->assertRedirect();

        Mail::assertNotQueued(EvaluadoAsignadoMail::class);

        $evaluado = EvaluadoOrden::where('email', 'sin.correo@evaluado.test')->first();
        $this->assertNotNull($evaluado);
        $this->assertSame('link_pendiente', $evaluado->estado_formulario);
    }

    public function test_reenviar_correo_avisa_si_la_empresa_esta_apagada(): void
    {
        $empresa = Empresa::factory()->create(['correos_automaticos' => false]);
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $this->admin->id,
        ]);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'email' => 'evaluado@test.com',
            'cuestionario_completado' => false,
            'token_expira_at' => now()->addDays(30),
        ]);

        $this->actingAs($this->admin)
            ->post(route('evaluados.reenviar-correo', $evaluado))
            ->assertRedirect()
            ->assertSessionHas('warning');

        Mail::assertNotQueued(EvaluadoAsignadoMail::class);
    }

    public function test_recordatorio_no_sale_si_la_empresa_esta_apagada(): void
    {
        $empresa = Empresa::factory()->create(['estado' => 1, 'correos_automaticos' => false]);
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'email' => 'recordatorio.apagado@test.com',
            'cuestionario_completado' => false,
            'token_unico' => 'token-apagado',
            'token_expira_at' => now()->addDays(3),
        ]);

        $this->artisan('notificaciones:recordatorios --dias=3')
            ->assertExitCode(0);

        Mail::assertNotQueued(RecordatorioCuestionarioMail::class);
        $this->assertNull($evaluado->fresh()->notificado_at);
    }

    public function test_la_cita_se_guarda_sin_correo_si_la_empresa_esta_apagada(): void
    {
        try {
            Carbon::setTestNow(Carbon::parse('2026-03-01 08:00:00'));

            $empresa = Empresa::factory()->create(['correos_automaticos' => false]);
            $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);
            $sede = Sede::factory()->create(['estado' => 1, 'nombre' => 'Sede Centro']);
            $poligrafista = User::factory()->create(['role_as' => 2, 'estado' => 1]);
            $poligrafista->roles()->attach(Role::where('name', 'repro')->first());
            $evaluado = EvaluadoOrden::factory()->create([
                'orden_id' => $orden->id,
                'email' => 'candidato.cita@test.com',
                'estado_evaluacion' => 'pendiente_de_evaluacion',
                'fecha_programada' => null,
            ]);

            $this->actingAs($poligrafista)
                ->post(route('calendario.programar'), [
                    'evaluado_orden_id' => $evaluado->id,
                    'fecha' => '2026-03-20',
                    'hora_inicio' => '09:00',
                    'hora_fin' => '11:00',
                    'sede_id' => $sede->id,
                    'poligrafista_id' => $poligrafista->id,
                    'modalidad' => 'presencial',
                ])
                ->assertRedirect()
                ->assertSessionHas('warning');

            $this->assertNotNull($evaluado->fresh()->fecha_programada);
            Mail::assertNotQueued(CitaProgramadaMail::class);
        } finally {
            Carbon::setTestNow();
        }
    }
}
