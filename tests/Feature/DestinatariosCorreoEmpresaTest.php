<?php

namespace Tests\Feature;

use App\Mail\ResultadosDisponiblesMail;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Role;
use App\Models\User;
use App\Notifications\ResultadoPreliminarNotification;
use App\Notifications\ResultadosDisponiblesNotification;
use App\Support\DestinatariosCorreoEmpresaSupport;
use App\Support\EmpresaPermisosSupport;
use App\Support\EmpresaVisibilidadReclutadoresSupport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

class DestinatariosCorreoEmpresaTest extends TestCase
{
    use RefreshDatabase, CreatesRolesAndPermissions;

    private Empresa $empresa;

    private User $principal;

    private User $reclutadorA;

    private User $reclutadorB;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
        Mail::fake();
        Notification::fake();

        $empresaRole = Role::where('name', 'empresa')->firstOrFail();
        $permisosTrabajador = json_encode(EmpresaPermisosSupport::permisosDefaultTrabajador());

        $this->empresa = Empresa::factory()->create([
            'email' => 'ficha.empresa@cliente.test',
            'modo_visibilidad_reclutadores' => EmpresaVisibilidadReclutadoresSupport::MODO_COMPARTIDO,
        ]);

        $this->principal = User::factory()->create([
            'name' => 'Gerente RRHH',
            'email' => 'gerente@cliente.test',
            'role_as' => 1,
            'empresa_id' => $this->empresa->id,
            'principal' => 1,
            'estado' => 1,
        ]);
        $this->principal->roles()->sync([$empresaRole->id]);

        $this->reclutadorA = User::factory()->create([
            'name' => 'Reclutador A',
            'email' => 'reclutador.a@cliente.test',
            'role_as' => 1,
            'empresa_id' => $this->empresa->id,
            'principal' => 0,
            'estado' => 1,
            'permisos' => $permisosTrabajador,
        ]);
        $this->reclutadorA->roles()->sync([$empresaRole->id]);

        $this->reclutadorB = User::factory()->create([
            'name' => 'Reclutador B',
            'email' => 'reclutador.b@cliente.test',
            'role_as' => 1,
            'empresa_id' => $this->empresa->id,
            'principal' => 0,
            'estado' => 1,
            'permisos' => $permisosTrabajador,
        ]);
        $this->reclutadorB->roles()->sync([$empresaRole->id]);

        $this->admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);
        $this->admin->roles()->sync([Role::where('name', 'admin')->firstOrFail()->id]);
    }

    public function test_con_reclutador_el_correo_va_solo_a_el_aunque_no_sea_confidencial(): void
    {
        $orden = $this->orden([
            'tipo_creador' => 'empresa',
            'creado_por' => $this->principal->id,
            'reclutador_id' => $this->reclutadorA->id,
            'confidencial' => false,
        ]);

        $this->assertDestinatarios($orden, [$this->reclutadorA]);
        $this->assertTrue(
            EmpresaVisibilidadReclutadoresSupport::puedeVerOrden($this->reclutadorB, $orden),
            'Sin confidencial el resto de la empresa sigue viendo la orden en SIGOR'
        );
    }

    public function test_sin_reclutador_creada_por_empresa_va_al_creador(): void
    {
        $orden = $this->orden([
            'tipo_creador' => 'empresa',
            'creado_por' => $this->reclutadorA->id,
            'reclutador_id' => null,
            'confidencial' => false,
        ]);

        $this->assertDestinatarios($orden, [$this->reclutadorA]);
    }

    public function test_creada_por_repro_sin_reclutador_va_al_titular(): void
    {
        $orden = $this->orden([
            'tipo_creador' => 'repro',
            'creado_por' => $this->admin->id,
            'reclutador_id' => null,
            'confidencial' => false,
        ]);

        $this->assertDestinatarios($orden, [$this->principal]);
    }

    public function test_confidencial_no_ensancha_el_correo_al_gerente(): void
    {
        $orden = $this->orden([
            'tipo_creador' => 'empresa',
            'creado_por' => $this->reclutadorA->id,
            'reclutador_id' => $this->reclutadorA->id,
            'confidencial' => true,
        ]);

        $this->assertDestinatarios($orden, [$this->reclutadorA]);
        $this->assertTrue(EmpresaVisibilidadReclutadoresSupport::puedeVerOrden($this->principal, $orden));
        $this->assertFalse(EmpresaVisibilidadReclutadoresSupport::puedeVerOrden($this->reclutadorB, $orden));
    }

    public function test_reclutador_inactivo_cae_al_titular_si_la_creo_repro(): void
    {
        $this->reclutadorA->update(['estado' => 0]);

        $orden = $this->orden([
            'tipo_creador' => 'repro',
            'creado_por' => $this->admin->id,
            'reclutador_id' => $this->reclutadorA->id,
            'confidencial' => false,
        ]);

        $this->assertDestinatarios($orden, [$this->principal]);
    }

    public function test_sin_usuarios_activos_usa_email_de_la_ficha(): void
    {
        $this->principal->update(['estado' => 0]);
        $this->reclutadorA->update(['estado' => 0]);
        $this->reclutadorB->update(['estado' => 0]);

        $orden = $this->orden([
            'tipo_creador' => 'repro',
            'creado_por' => $this->admin->id,
            'reclutador_id' => null,
        ]);

        $this->assertTrue(DestinatariosCorreoEmpresaSupport::usuariosResultados($orden)->isEmpty());
        $this->assertEquals(
            ['ficha.empresa@cliente.test'],
            DestinatariosCorreoEmpresaSupport::emailsResultados($orden)->all()
        );
    }

    public function test_campana_visible_omite_reclutador_ajeno_en_confidencial(): void
    {
        $orden = $this->orden([
            'tipo_creador' => 'empresa',
            'creado_por' => $this->reclutadorA->id,
            'reclutador_id' => $this->reclutadorA->id,
            'confidencial' => true,
        ]);

        $ids = DestinatariosCorreoEmpresaSupport::usuariosVisiblesEmpresa($orden)->pluck('id');

        $this->assertTrue($ids->contains($this->principal->id));
        $this->assertTrue($ids->contains($this->reclutadorA->id));
        $this->assertFalse($ids->contains($this->reclutadorB->id));
    }

    public function test_liberar_resultados_manda_correo_solo_al_reclutador(): void
    {
        $orden = $this->orden([
            'tipo_creador' => 'repro',
            'creado_por' => $this->admin->id,
            'reclutador_id' => $this->reclutadorA->id,
            'confidencial' => false,
            'resultados_visibles_empresa' => false,
        ]);
        EvaluadoOrden::factory()->create(['orden_id' => $orden->id]);

        $this->actingAs($this->admin)
            ->patch(route('ordenes.toggle-resultados-visibles', $orden))
            ->assertRedirect();

        Mail::assertQueued(ResultadosDisponiblesMail::class, function (ResultadosDisponiblesMail $mail) {
            return $mail->hasTo('reclutador.a@cliente.test');
        });
        Mail::assertNotQueued(ResultadosDisponiblesMail::class, function (ResultadosDisponiblesMail $mail) {
            return $mail->hasTo('gerente@cliente.test') || $mail->hasTo('reclutador.b@cliente.test');
        });

        Notification::assertSentTo($this->reclutadorA, ResultadosDisponiblesNotification::class);
        Notification::assertNotSentTo($this->principal, ResultadosDisponiblesNotification::class);
        Notification::assertNotSentTo($this->reclutadorB, ResultadosDisponiblesNotification::class);
    }

    public function test_preliminar_campana_sigue_la_misma_matriz(): void
    {
        $orden = $this->orden([
            'tipo_creador' => 'repro',
            'creado_por' => $this->admin->id,
            'reclutador_id' => $this->reclutadorA->id,
            'resultados_visibles_empresa' => true,
        ]);
        $evaluado = EvaluadoOrden::factory()->create(['orden_id' => $orden->id]);

        $this->actingAs($this->admin)
            ->patch(route('evaluados.guardar-informe-preliminar', $evaluado), [
                'texto_informe_preliminar' => '<p>Informe</p>',
            ])
            ->assertRedirect();

        Notification::assertSentTo($this->reclutadorA, ResultadoPreliminarNotification::class);
        Notification::assertNotSentTo($this->principal, ResultadoPreliminarNotification::class);
        Notification::assertNotSentTo($this->reclutadorB, ResultadoPreliminarNotification::class);
    }

    /**
     * @param  array<string, mixed>  $attrs
     */
    private function orden(array $attrs): Orden
    {
        return Orden::factory()->create(array_merge([
            'empresa_id' => $this->empresa->id,
        ], $attrs));
    }

    /**
     * @param  list<User>  $esperados
     */
    private function assertDestinatarios(Orden $orden, array $esperados): void
    {
        $usuarios = DestinatariosCorreoEmpresaSupport::usuariosResultados($orden);
        $this->assertEqualsCanonicalizing(
            collect($esperados)->pluck('id')->all(),
            $usuarios->pluck('id')->all()
        );
        $this->assertEqualsCanonicalizing(
            collect($esperados)->pluck('email')->map(fn ($e) => strtolower((string) $e))->all(),
            DestinatariosCorreoEmpresaSupport::emailsResultados($orden)->all()
        );
    }
}
