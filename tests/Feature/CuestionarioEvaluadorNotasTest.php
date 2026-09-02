<?php

namespace Tests\Feature;

use App\Models\Cuestionario;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\EvaluadorNota;
use App\Models\Orden;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

class CuestionarioEvaluadorNotasTest extends TestCase
{
    use RefreshDatabase, CreatesRolesAndPermissions;

    private Cuestionario $cuestionario;

    private Empresa $empresaCliente;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpRolesAndPermissions();

        $this->empresaCliente = Empresa::factory()->create();
        $orden = Orden::factory()->create(['empresa_id' => $this->empresaCliente->id]);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'token_unico' => 'token-notas-evaluador',
            'token_expira_at' => now()->addDays(30),
        ]);

        $this->cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 1,
            'total_secciones' => 6,
            'progreso_porcentaje' => 10,
            'estado' => 'en_progreso',
            'completado' => false,
        ]);
    }

    private function crearAdmin(): User
    {
        $user = User::factory()->create(['role_as' => 3, 'estado' => 1]);
        $user->roles()->attach(Role::where('name', 'admin')->first());

        return $user;
    }

    private function crearRepro(): User
    {
        $user = User::factory()->create(['role_as' => 2, 'estado' => 1]);
        $user->roles()->attach(Role::where('name', 'repro')->first());

        return $user;
    }

    private function crearEmpresa(): User
    {
        $user = User::factory()->create([
            'role_as' => 1,
            'estado' => 1,
            'empresa_id' => $this->empresaCliente->id,
        ]);
        $user->roles()->attach(Role::where('name', 'empresa')->first());

        return $user;
    }

    public function test_repro_ve_ui_notas_evaluador_en_show_y_edit(): void
    {
        $repro = $this->crearRepro();

        $this->actingAs($repro)
            ->get(route('admin.cuestionarios.show', $this->cuestionario->id))
            ->assertOk()
            ->assertSee('Notas internas del evaluador')
            ->assertSee('Información Personal');

        $this->actingAs($repro)
            ->get(route('admin.cuestionarios.edit', $this->cuestionario->id))
            ->assertOk()
            ->assertSee('name="evaluador_notas[datos_personales]"', false)
            ->assertSee('Descargar borrador Word')
            ->assertSee('Recomendaciones del informe')
            ->assertSee('name="evaluador_notas[word_recomendaciones]"', false);
    }

    public function test_edit_separa_cuestionario_rojo_de_redaccion_word(): void
    {
        $repro = $this->crearRepro();

        $this->actingAs($repro)
            ->get(route('admin.cuestionarios.edit', $this->cuestionario->id))
            ->assertOk()
            ->assertSee('Editar contenido de cuestionario')
            ->assertSee('PDF de lo que llenó el candidato')
            ->assertSee('seccion-editar-cuestionario', false)
            ->assertSee('bg-danger', false)
            ->assertSee('Inicio de redacción de informe en Word')
            ->assertSeeInOrder([
                'Editar contenido de cuestionario',
                'Inicio de redacción de informe en Word',
                'Resultado de evaluación (primera hoja del informe)',
                'Tablas para informe',
                'Redacción del informe Word',
            ]);
    }

    public function test_show_pone_tablas_informe_antes_de_redaccion_word(): void
    {
        $repro = $this->crearRepro();

        $this->actingAs($repro)
            ->get(route('admin.cuestionarios.show', $this->cuestionario->id))
            ->assertOk()
            ->assertSeeInOrder([
                'Inicio de redacción de informe en Word',
                'Resultado de evaluación (primera hoja del informe)',
                'Tablas para informe',
                'Redacción del informe Word',
            ]);
    }

    public function test_repro_guarda_notas_por_seccion(): void
    {
        $repro = $this->crearRepro();

        $this->actingAs($repro)
            ->put(route('admin.cuestionarios.update', $this->cuestionario->id), [
                'evaluador_notas' => [
                    'datos_personales' => 'Observación sobre identidad del candidato.',
                    'informacion_familiar' => 'Familia estable, sin alertas.',
                ],
            ])
            ->assertRedirect(route('admin.cuestionarios.edit', $this->cuestionario->id));

        $this->assertDatabaseHas('evaluador_notas', [
            'evaluado_orden_id' => $this->cuestionario->evaluado_orden_id,
            'seccion' => 'datos_personales',
            'campo' => '',
            'contenido' => 'Observación sobre identidad del candidato.',
            'user_id' => $repro->id,
        ]);

        $this->assertDatabaseHas('evaluador_notas', [
            'evaluado_orden_id' => $this->cuestionario->evaluado_orden_id,
            'seccion' => 'informacion_familiar',
            'contenido' => 'Familia estable, sin alertas.',
        ]);
    }

    public function test_empresa_no_ve_notas_evaluador_en_portal(): void
    {
        EvaluadorNota::guardarNota(
            $this->cuestionario->evaluado_orden_id,
            'datos_personales',
            '',
            'Nota confidencial REPRO',
            $this->crearAdmin()->id
        );

        $evaluado = $this->cuestionario->evaluadoOrden;
        $empresaUser = $this->crearEmpresa();

        $this->actingAs($empresaUser)
            ->get(route('empresa.cuestionarios.show', $evaluado->id))
            ->assertOk()
            ->assertDontSee('Notas internas del evaluador')
            ->assertDontSee('Nota confidencial REPRO');
    }

    public function test_pdf_admin_no_incluye_notas_evaluador(): void
    {
        EvaluadorNota::guardarNota(
            $this->cuestionario->evaluado_orden_id,
            'datos_personales',
            '',
            'Análisis interno no exportable',
            $this->crearAdmin()->id
        );

        $admin = $this->crearAdmin();
        $cuestionario = Cuestionario::with([
            'evaluadoOrden.orden.empresa',
            'respuestas',
        ])->find($this->cuestionario->id);

        $html = view('admin.cuestionarios.pdf', [
            'cuestionario' => $cuestionario,
            'respuestasPorSeccion' => $cuestionario->respuestas->groupBy('seccion'),
            'imagen' => null,
        ])->render();

        $this->assertStringNotContainsString('Análisis interno no exportable', $html);
        $this->assertStringNotContainsString('Notas internas del evaluador', $html);
    }

    public function test_usuario_sin_rol_repro_no_puede_enviar_notas(): void
    {
        $empresaUser = User::factory()->create(['role_as' => 1, 'estado' => 1]);
        $empresaUser->roles()->attach(Role::where('name', 'empresa')->first());

        $this->actingAs($empresaUser)
            ->put(route('admin.cuestionarios.update', $this->cuestionario->id), [
                'evaluador_notas' => ['datos_personales' => 'Intento no autorizado'],
            ])
            ->assertForbidden();
    }
}
