<?php

namespace Tests\Feature;

use App\Models\Config;
use App\Models\Cuestionario;
use App\Models\CuestionarioRespuesta;
use App\Models\EvaluadoOrden;
use App\Models\EvaluadorNota;
use App\Models\Orden;
use App\Models\User;
use App\Support\AsistenteRedaccionMotor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Doubles\AsistenteRedaccionMotorFijo;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

class AsistenteRedaccionCicloTest extends TestCase
{
    use RefreshDatabase;
    use CreatesRolesAndPermissions;

    private Cuestionario $cuestionario;

    private AsistenteRedaccionMotorFijo $motor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
        Http::preventStrayRequests();

        $this->motor = new AsistenteRedaccionMotorFijo;
        $this->app->instance(AsistenteRedaccionMotor::class, $this->motor);

        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
            'nombre' => 'Cristopher Unico',
            'dpi' => '3403384231403',
            'resultado' => 'aprobado',
            'informe_preliminar_at' => null,
        ]);
        $this->cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 5,
            'total_secciones' => 5,
            'progreso_porcentaje' => 100,
            'completado' => true,
            'bloqueado' => false,
            'terminos_aceptados' => true,
            'terminos_aceptados_at' => now(),
        ]);
        CuestionarioRespuesta::guardarRespuestas($this->cuestionario->id, 'antecedentes', [
            'salud_estado_general' => 'buena',
            'salud_alergias' => '',
        ]);
        EvaluadorNota::guardarNota($evaluado->id, 'word_salud', '', 'Texto ya guardado', null);
        EvaluadorNota::guardarNota($evaluado->id, 'word_judicial', '', 'Judicial intacto', null);
    }

    public function test_apagado_no_hay_boton_y_no_redacta(): void
    {
        Config::create(['currency' => 'GTQ Q', 'asistente_redaccion_activo' => false]);
        $repro = $this->repro();

        $this->actingAs($repro)
            ->get(route('admin.cuestionarios.edit', $this->cuestionario))
            ->assertOk()
            ->assertDontSee('btn-asistente-redaccion', false);

        $this->actingAs($repro)
            ->postJson(route('admin.cuestionarios.asistente-redaccion', $this->cuestionario), [
                'seccion' => 'word_salud',
                'modo' => 'confirmada',
            ])
            ->assertNotFound();

        $this->assertSame(0, $this->motor->llamadas);
        $this->assertDatabaseHas('evaluador_notas', [
            'seccion' => 'word_salud',
            'contenido' => 'Texto ya guardado',
        ]);
    }

    public function test_generar_muestra_propuesta_sin_guardarla_ni_enviar_identidad(): void
    {
        $this->encender();
        $repro = $this->repro();

        $this->actingAs($repro)
            ->get(route('admin.cuestionarios.edit', $this->cuestionario))
            ->assertOk()
            ->assertSee('btn-asistente-redaccion', false)
            ->assertSee('data-seccion="word_salud"', false)
            ->assertSee('data-seccion="word_recomendaciones"', false)
            ->assertSee('Dictar en la nota', false)
            ->assertDontSee('data-seccion="word_habitos"', false);

        $this->actingAs($repro)
            ->get(route('admin.cuestionarios.show', $this->cuestionario))
            ->assertOk()
            ->assertDontSee('btn-asistente-redaccion', false);

        $respuesta = $this->actingAs($repro)
            ->postJson(route('admin.cuestionarios.asistente-redaccion', $this->cuestionario), [
                'seccion' => 'word_salud',
                'modo' => 'observaciones',
                'observacion' => 'Durante la entrevista refiere alergia a la penicilina.',
                'resto_sin_cambios' => 1,
            ])
            ->assertOk()
            ->assertJsonPath('texto', 'PROPUESTA DE PRUEBA');

        $this->assertNotEmpty($respuesta->json('avisos'));
        $this->assertSame(1, $this->motor->llamadas);
        $this->assertStringContainsString('(vacío)', $this->motor->usuario);
        $this->assertStringContainsString('penicilina', $this->motor->usuario);
        $this->assertStringContainsString('polígrafo', $this->motor->sistema);
        $this->assertStringNotContainsString('Cristopher Unico', $this->motor->usuario);
        $this->assertStringNotContainsString('3403384231403', $this->motor->usuario);
        $this->assertStringNotContainsString('Texto ya guardado', $this->motor->usuario);
        $this->assertDatabaseHas('evaluador_notas', [
            'seccion' => 'word_salud',
            'contenido' => 'Texto ya guardado',
        ]);
        $this->assertDatabaseMissing('evaluador_notas', [
            'contenido' => 'PROPUESTA DE PRUEBA',
        ]);
    }

    public function test_aceptar_guarda_solo_esa_nota(): void
    {
        $this->encender();
        $repro = $this->repro();

        $this->actingAs($repro)
            ->postJson(route('admin.cuestionarios.asistente-redaccion.aceptar', $this->cuestionario), [
                'seccion' => 'word_salud',
                'texto' => 'Refiere buen estado de salud. Durante la entrevista informó alergia a la penicilina.',
            ])
            ->assertOk();

        $this->assertDatabaseHas('evaluador_notas', [
            'seccion' => 'word_salud',
            'contenido' => 'Refiere buen estado de salud. Durante la entrevista informó alergia a la penicilina.',
        ]);
        $this->assertDatabaseHas('evaluador_notas', [
            'seccion' => 'word_judicial',
            'contenido' => 'Judicial intacto',
        ]);
        $this->assertSame('aprobado', $this->cuestionario->evaluadoOrden->fresh()->resultado);
        $this->assertNull($this->cuestionario->evaluadoOrden->fresh()->informe_preliminar_at);
        $this->assertSame(0, $this->motor->llamadas);
    }

    public function test_reescribir_otra_version_y_estilo_no_guardan(): void
    {
        $this->encender();
        $repro = $this->repro();

        $this->actingAs($repro)
            ->postJson(route('admin.cuestionarios.asistente-redaccion', $this->cuestionario), [
                'seccion' => 'word_salud',
                'modo' => 'cambiar_estilo',
                'estilo' => 'breve',
                'texto_actual' => 'Refiere buen estado de salud y alergia a la penicilina.',
            ])
            ->assertOk();

        $this->assertStringContainsString('breve', $this->motor->usuario);
        $this->assertStringContainsString('penicilina', $this->motor->usuario);
        $this->assertDatabaseHas('evaluador_notas', [
            'seccion' => 'word_salud',
            'contenido' => 'Texto ya guardado',
        ]);
    }

    public function test_habitos_no_usa_el_asistente(): void
    {
        $this->encender();

        $this->actingAs($this->repro())
            ->postJson(route('admin.cuestionarios.asistente-redaccion', $this->cuestionario), [
                'seccion' => 'word_habitos',
                'modo' => 'confirmada',
            ])
            ->assertStatus(422);

        $this->assertSame(0, $this->motor->llamadas);
    }

    public function test_la_empresa_no_puede_generar(): void
    {
        $this->encender();
        $empresa = User::factory()->create(['role_as' => 1, 'estado' => 1]);
        $empresa->roles()->attach(\App\Models\Role::where('name', 'empresa')->first());

        $this->actingAs($empresa)
            ->postJson(route('admin.cuestionarios.asistente-redaccion', $this->cuestionario), [
                'seccion' => 'word_salud',
                'modo' => 'confirmada',
            ])
            ->assertForbidden();
    }

    private function encender(): void
    {
        Config::create(['currency' => 'GTQ Q', 'asistente_redaccion_activo' => true]);
    }

    private function repro(): User
    {
        $user = User::factory()->create(['role_as' => 2, 'estado' => 1]);
        $user->roles()->attach(\App\Models\Role::where('name', 'repro')->first());

        return $user;
    }
}
