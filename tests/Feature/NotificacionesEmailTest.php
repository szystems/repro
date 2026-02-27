<?php

namespace Tests\Feature;

use App\Mail\CuestionarioCompletadoMail;
use App\Mail\EvaluadoAsignadoMail;
use App\Mail\RecordatorioCuestionarioMail;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotificacionesEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear roles necesarios
        Role::create(['name' => 'admin', 'display_name' => 'Administrador', 'description' => 'Admin']);
        Role::create(['name' => 'repro', 'display_name' => 'REPRO', 'description' => 'REPRO']);
        Role::create(['name' => 'empresa', 'display_name' => 'Empresa', 'description' => 'Empresa']);
    }

    /**
     * Test que el Mailable EvaluadoAsignadoMail se puede construir correctamente.
     */
    public function test_evaluado_asignado_mail_can_be_rendered(): void
    {
        $empresa = Empresa::factory()->create(['estado' => 1]);
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'email' => 'evaluado@test.com',
            'token_unico' => 'test-token-123',
            'token_expira_at' => now()->addDays(30),
        ]);

        $mailable = new EvaluadoAsignadoMail($evaluado);

        $mailable->assertSeeInHtml($evaluado->nombre);
        $mailable->assertSeeInHtml('test-token-123');
        $mailable->assertSeeInHtml('REPRO Guatemala');
    }

    /**
     * Test que el Mailable RecordatorioCuestionarioMail se puede construir correctamente.
     */
    public function test_recordatorio_cuestionario_mail_can_be_rendered(): void
    {
        $empresa = Empresa::factory()->create(['estado' => 1]);
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'email' => 'evaluado@test.com',
            'token_unico' => 'test-token-456',
            'token_expira_at' => now()->addDays(3),
        ]);

        $mailable = new RecordatorioCuestionarioMail($evaluado, 3);

        $mailable->assertSeeInHtml($evaluado->nombre);
        $mailable->assertSeeInHtml('3');
        $mailable->assertSeeInHtml('días restantes');
    }

    /**
     * Test que el Mailable RecordatorioCuestionarioMail muestra urgencia cuando queda 1 día.
     */
    public function test_recordatorio_cuestionario_mail_shows_urgency_for_one_day(): void
    {
        $empresa = Empresa::factory()->create(['estado' => 1]);
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'email' => 'evaluado@test.com',
            'token_expira_at' => now()->addDay(),
        ]);

        $mailable = new RecordatorioCuestionarioMail($evaluado, 1);

        $mailable->assertSeeInHtml('URGENTE');
        $mailable->assertSeeInHtml('día restante');
    }

    /**
     * Test que el Mailable CuestionarioCompletadoMail se puede construir correctamente.
     */
    public function test_cuestionario_completado_mail_can_be_rendered(): void
    {
        $empresa = Empresa::factory()->create(['estado' => 1, 'nombre' => 'Empresa Test']);
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'nombre' => 'Juan',
            'apellidos' => 'Pérez',
            'email' => 'juan@test.com',
            'completado_at' => now(),
        ]);

        $mailable = new CuestionarioCompletadoMail($evaluado);

        $mailable->assertSeeInHtml('Juan');
        $mailable->assertSeeInHtml('Pérez');
        $mailable->assertSeeInHtml('Cuestionario Completado');
        $mailable->assertSeeInHtml('Empresa Test');
    }

    /**
     * Test que se envía email cuando se crea un evaluado con email.
     */
    public function test_email_is_sent_when_evaluado_is_created(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        $empresa = Empresa::factory()->create(['estado' => 1]);
        
        $response = $this->actingAs($admin)->post(route('ordenes.store'), [
            'empresa_id' => $empresa->id,
            'observaciones_internas' => 'Test orden',
            'prioridad' => 'normal',
            'evaluados' => [
                [
                    'nombre' => 'Test',
                    'apellidos' => 'Evaluado',
                    'dpi' => '1234567890123',
                    'email' => 'test@evaluado.com',
                    'tipo_servicio' => 'poligrafo',
                    'tipo_formulario' => 'preempleo',
                ],
            ],
        ]);

        // Verificar que se envió el email
        Mail::assertQueued(EvaluadoAsignadoMail::class, function ($mail) {
            return $mail->hasTo('test@evaluado.com');
        });
    }

    /**
     * Test que NO se envía email si el evaluado no tiene email.
     */
    public function test_email_is_not_sent_when_evaluado_has_no_email(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        $empresa = Empresa::factory()->create(['estado' => 1]);
        
        $response = $this->actingAs($admin)->post(route('ordenes.store'), [
            'empresa_id' => $empresa->id,
            'observaciones_internas' => 'Test orden sin email',
            'prioridad' => 'normal',
            'evaluados' => [
                [
                    'nombre' => 'Test',
                    'apellidos' => 'Sin Email',
                    'dpi' => '9876543210123',
                    'tipo_servicio' => 'poligrafo',
                    'tipo_formulario' => 'preempleo',
                ],
            ],
        ]);

        // Verificar que NO se envió el email
        Mail::assertNotQueued(EvaluadoAsignadoMail::class);
    }

    /**
     * Test del comando de recordatorios.
     */
    public function test_recordatorios_command_sends_emails(): void
    {
        Mail::fake();

        $empresa = Empresa::factory()->create(['estado' => 1]);
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);
        
        // Crear evaluado que expira en 3 días
        EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'email' => 'recordatorio@test.com',
            'cuestionario_completado' => false,
            'token_unico' => 'token-recordatorio',
            'token_expira_at' => now()->addDays(3),
        ]);

        $this->artisan('notificaciones:recordatorios --dias=3')
            ->assertExitCode(0);

        Mail::assertQueued(RecordatorioCuestionarioMail::class, function ($mail) {
            return $mail->hasTo('recordatorio@test.com');
        });
    }

    /**
     * Test que el comando no envía recordatorios a evaluados que ya completaron.
     */
    public function test_recordatorios_command_skips_completed_evaluados(): void
    {
        Mail::fake();

        $empresa = Empresa::factory()->create(['estado' => 1]);
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);
        
        // Crear evaluado que ya completó
        EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'email' => 'completado@test.com',
            'cuestionario_completado' => true,
            'token_unico' => 'token-completado',
            'token_expira_at' => now()->addDays(3),
        ]);

        $this->artisan('notificaciones:recordatorios --dias=3')
            ->assertExitCode(0);

        Mail::assertNotQueued(RecordatorioCuestionarioMail::class);
    }
}
