<?php

namespace Tests\Feature;

use App\Mail\ResultadosDisponiblesMail;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

class ResultadoPreliminarCorreoTest extends TestCase
{
    use RefreshDatabase, CreatesRolesAndPermissions;

    private User $admin;

    private Orden $orden;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
        Mail::fake();

        $empresa = Empresa::factory()->create(['email' => 'contacto@cliente.test']);
        $this->admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);
        $this->admin->roles()->attach(Role::where('name', 'admin')->first());
        $this->orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $this->admin->id,
            'resultados_visibles_empresa' => false,
        ]);
        EvaluadoOrden::factory()->create(['orden_id' => $this->orden->id]);
    }

    public function test_liberar_sin_informe_final_dice_resultado_preliminar(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('ordenes.toggle-resultados-visibles', $this->orden))
            ->assertRedirect();

        Mail::assertQueued(ResultadosDisponiblesMail::class, function (ResultadosDisponiblesMail $mail) {
            $html = $mail->render();

            return $mail->esPreliminar()
                && str_contains($mail->envelope()->subject, 'Resultado preliminar disponible')
                && str_contains($mail->envelope()->subject, $this->orden->codigo_orden)
                && str_contains($html, 'Resultado preliminar disponible')
                && str_contains($html, 'a la brevedad posible')
                && ! str_contains($html, 'Los resultados de su orden ya están disponibles');
        });
    }

    public function test_liberar_con_informe_final_no_lo_presenta_como_preliminar(): void
    {
        $this->orden->evaluados()->first()->update([
            'archivo_resultado_final' => 'resultados/1/final.pdf',
        ]);

        $this->actingAs($this->admin)
            ->patch(route('ordenes.toggle-resultados-visibles', $this->orden))
            ->assertRedirect();

        Mail::assertQueued(ResultadosDisponiblesMail::class, function (ResultadosDisponiblesMail $mail) {
            $html = $mail->render();

            return ! $mail->esPreliminar()
                && str_contains($mail->envelope()->subject, 'Informe final disponible')
                && str_contains($html, 'Informe final disponible')
                && ! str_contains($html, 'Resultado preliminar disponible')
                && ! str_contains($html, 'a la brevedad posible');
        });
    }

    public function test_subir_archivo_preliminar_envia_el_aviso_preliminar(): void
    {
        $evaluado = $this->orden->evaluados()->first();

        $this->actingAs($this->admin)
            ->post(route('evaluados.subir-resultado-archivo', $evaluado), [
                'tipo_resultado' => 'preliminar',
                'archivo' => \Illuminate\Http\UploadedFile::fake()->create('preliminar.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect();

        Mail::assertQueued(ResultadosDisponiblesMail::class, function (ResultadosDisponiblesMail $mail) {
            return $mail->esPreliminar()
                && str_contains($mail->render(), 'El <strong>informe final</strong> se estará completando a la brevedad posible.');
        });
    }

    public function test_si_la_campana_falla_no_dice_que_el_correo_no_salio(): void
    {
        $this->app->bind(\Illuminate\Notifications\Channels\DatabaseChannel::class, function () {
            return new class extends \Illuminate\Notifications\Channels\DatabaseChannel
            {
                public function send($notifiable, \Illuminate\Notifications\Notification $notification)
                {
                    throw new \RuntimeException('campana rota');
                }
            };
        });

        $evaluado = $this->orden->evaluados()->first();

        $this->actingAs($this->admin)
            ->patch(route('evaluados.guardar-informe-preliminar', $evaluado), [
                'texto_informe_preliminar' => '<p>Informe</p>',
            ])
            ->assertRedirect()
            ->assertSessionHas('success')
            ->assertSessionMissing('warning');

        Mail::assertQueued(ResultadosDisponiblesMail::class);
    }

    public function test_si_el_correo_falla_el_aviso_sigue_apareciendo(): void
    {
        $pending = \Mockery::mock();
        $pending->shouldReceive('send')->once()->andThrow(new \RuntimeException('smtp rechazó el mensaje'));
        Mail::shouldReceive('to')->once()->andReturn($pending);

        $evaluado = $this->orden->evaluados()->first();

        $this->actingAs($this->admin)
            ->patch(route('evaluados.guardar-informe-preliminar', $evaluado), [
                'texto_informe_preliminar' => '<p>Informe</p>',
            ])
            ->assertRedirect()
            ->assertSessionHas('success')
            ->assertSessionHas('warning', 'El cambio quedó en el portal, pero el correo no se pudo enviar.');
    }
}
