<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Role;
use App\Models\User;
use App\Notifications\CuestionarioCompletadoNotification;
use App\Notifications\EvaluadoAsignadoNotification;
use App\Notifications\OrdenCreadaNotification;
use App\Notifications\ResultadoPreliminarNotification;
use App\Notifications\ResultadosDisponiblesNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

class NotificacionesInAppTest extends TestCase
{
    use RefreshDatabase, CreatesRolesAndPermissions;

    private User $admin;
    private User $repro;
    private User $empresa;
    private Empresa $empresaModel;
    private Orden $orden;
    private EvaluadoOrden $evaluado;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpRolesAndPermissions();

        $this->admin        = User::factory()->create(['role_as' => 3, 'estado' => 1]);
        $this->repro        = User::factory()->create(['role_as' => 2, 'estado' => 1]);
        $this->repro->roles()->attach(Role::where('name', 'repro')->first());
        $this->empresaModel = Empresa::factory()->create(['estado' => 1]);
        $this->empresa      = User::factory()->create(['role_as' => 1, 'estado' => 1, 'empresa_id' => $this->empresaModel->id]);
        $this->empresa->roles()->attach(Role::where('name', 'empresa')->first());

        $this->orden    = Orden::factory()->create(['empresa_id' => $this->empresaModel->id]);
        $this->evaluado = EvaluadoOrden::factory()->create([
            'orden_id'        => $this->orden->id,
            'token_unico'     => 'tok-test',
            'token_expira_at' => now()->addDays(30),
        ]);
    }

    /** Orden creada notifica a admin y repro */
    public function test_orden_creada_notifica_a_repro_y_admin(): void
    {
        Notification::fake();

        $this->actingAs($this->repro)->post(route('ordenes.store'), $this->ordenPayload());

        Notification::assertSentTo($this->admin, OrdenCreadaNotification::class);
    }

    /** Orden creada también notifica a usuarios de la empresa */
    public function test_orden_creada_notifica_a_empresa(): void
    {
        Notification::fake();

        $this->actingAs($this->admin)->post(route('ordenes.store'), $this->ordenPayload());

        Notification::assertSentTo($this->empresa, OrdenCreadaNotification::class);
    }

    /** Orden creada también notifica al creador (Fase 18) */
    public function test_orden_creada_notifica_al_creador(): void
    {
        Notification::fake();

        $this->actingAs($this->admin)->post(route('ordenes.store'), $this->ordenPayload());

        Notification::assertSentTo($this->admin, OrdenCreadaNotification::class);
    }

    /** EvaluadoAsignado in-app se envía a empresa cuando se crea orden */
    public function test_evaluado_asignado_notifica_a_empresa_en_creacion(): void
    {
        Notification::fake();

        $this->actingAs($this->admin)->post(route('ordenes.store'), $this->ordenPayload());

        Notification::assertSentTo($this->empresa, EvaluadoAsignadoNotification::class);
    }

    /** EvaluadoAsignado NO se envía a repro/admin durante creación (ya reciben OrdenCreada) */
    public function test_evaluado_asignado_no_notifica_a_repro_en_creacion(): void
    {
        Notification::fake();

        $this->actingAs($this->admin)->post(route('ordenes.store'), $this->ordenPayload());

        Notification::assertNotSentTo($this->repro, EvaluadoAsignadoNotification::class);
    }

    /** ResultadoPreliminar notifica a admin (no al uploader) y empresa */
    public function test_preliminar_subido_notifica_a_admin_y_empresa(): void
    {
        Notification::fake();

        $this->actingAs($this->repro)
            ->patch(route('evaluados.guardar-informe-preliminar', $this->evaluado), [
                'texto_informe_preliminar' => '<p>Informe de prueba</p>',
            ]);

        Notification::assertSentTo($this->admin, ResultadoPreliminarNotification::class);
        Notification::assertSentTo($this->empresa, ResultadoPreliminarNotification::class);
    }

    /** ResultadoPreliminar NO notifica al propio uploader */
    public function test_preliminar_subido_no_notifica_al_uploader(): void
    {
        Notification::fake();

        $this->actingAs($this->admin)
            ->patch(route('evaluados.guardar-informe-preliminar', $this->evaluado), [
                'texto_informe_preliminar' => '<p>Informe de prueba</p>',
            ]);

        Notification::assertNotSentTo($this->admin, ResultadoPreliminarNotification::class);
    }

    /** ResultadosDisponibles notifica a empresa cuando se libera el resultado */
    public function test_resultados_disponibles_notifica_a_empresa(): void
    {
        Notification::fake();

        $this->actingAs($this->repro)
            ->patch(route('evaluados.guardar-informe-preliminar', $this->evaluado), [
                'texto_informe_preliminar' => '<p>Informe</p>',
            ]);

        Notification::assertSentTo($this->empresa, ResultadosDisponiblesNotification::class);
    }

    // -----------------------------------------------------------------------

    private function ordenPayload(): array
    {
        $sede = \App\Models\Sede::factory()->create();

        return [
            'empresa_id' => $this->empresaModel->id,
            'sede_id'    => $sede->id,
            'evaluados'  => [
                [
                    'nombre'          => 'Candidato',
                    'apellidos'       => 'Test',
                    'dpi'             => '1234567890101',
                    'email'           => 'candidato@test.com',
                    'tipo_servicio'   => 'poligrafo',
                    'tipo_formulario' => 'preempleo',
                    'puesto_evaluar'  => 'Analista',
                ],
            ],
        ];
    }
}
