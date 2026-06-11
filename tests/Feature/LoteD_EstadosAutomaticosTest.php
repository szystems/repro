<?php

namespace Tests\Feature;

use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Tests Lote D — Estados automáticos (Fase 16).
 *
 * Cubre:
 * - 1a: Label docs_pendientes → "Formulario Recibido"
 * - 1b: Auto-estado en_proceso al subir preliminar
 * - 1c: Programar cita cambia estado a programado
 * - 2: Reprogramar redirige de vuelta a la página de origen
 */
class LoteD_EstadosAutomaticosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'admin', 'display_name' => 'Administrador']);
        Role::create(['name' => 'repro', 'display_name' => 'Repro']);
        Role::create(['name' => 'empresa', 'display_name' => 'Empresa']);
    }

    private function crearAdmin(): User
    {
        $user = User::factory()->create(['role_as' => 3, 'estado' => 1]);
        $user->roles()->attach(Role::where('name', 'admin')->first());
        return $user;
    }

    private function crearReproConPermiso(string $permiso): User
    {
        $user = User::factory()->create(['role_as' => 2, 'estado' => 1]);
        $reproRole = Role::where('name', 'repro')->first();
        $perm = Permission::firstOrCreate(
            ['name' => $permiso],
            ['display_name' => $permiso, 'module' => explode('.', $permiso)[0]]
        );
        $reproRole->givePermission($perm);
        $user->roles()->attach($reproRole);
        return $user;
    }

    // ─────────────────────────────────────────────────────
    // 1a — Label docs_pendientes → "Formulario Recibido"
    // ─────────────────────────────────────────────────────

    public function test_pendiente_evaluacion_label_es_pendiente_de_evaluacion(): void
    {
        // Fase 18: el estado inicial de evaluacion es pendiente_de_evaluacion
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id'          => Orden::factory()->create()->id,
            'estado_evaluacion' => 'pendiente_de_evaluacion',
        ]);

        $this->assertSame('Pendiente de Evaluación', $evaluado->estado_evaluacion_texto);
    }

    public function test_estadosEvaluacionDisponibles_tiene_label_pendiente_de_evaluacion(): void
    {
        $estados = EvaluadoOrden::estadosEvaluacionDisponibles();
        $this->assertSame('Pendiente de Evaluación', $estados['pendiente_de_evaluacion']);
    }

    // ─────────────────────────────────────────────────────
    // 1b — Auto-estado en_proceso al subir preliminar
    // ─────────────────────────────────────────────────────

    public function test_subir_preliminar_no_cambia_estado_evaluacion(): void
    {
        // Fase 18: subir preliminar NO cambia estado_evaluacion (es 100% manual, respuesta cliente #2)
        Storage::fake('local');

        $admin = $this->crearAdmin();
        $orden = Orden::factory()->create(['estado' => 'en_proceso']);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id'          => $orden->id,
            'estado_evaluacion' => 'en_revision',
        ]);

        $this->actingAs($admin)->post(route('evaluados.subir-resultado-archivo', $evaluado), [
            'tipo_resultado' => 'preliminar',
            'archivo'        => UploadedFile::fake()->create('informe.pdf', 500, 'application/pdf'),
        ])->assertRedirect();

        $evaluado->refresh();
        // Estado evaluacion NO cambia al subir preliminar
        $this->assertSame('en_revision', $evaluado->estado_evaluacion);
    }

    public function test_subir_preliminar_no_modifica_evaluado_ya_en_proceso(): void
    {
        Storage::fake('local');

        $admin = $this->crearAdmin();
        $orden = Orden::factory()->create(['estado' => 'en_proceso']);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id'          => $orden->id,
            'estado_evaluacion' => 'en_proceso',
        ]);

        $this->actingAs($admin)->post(route('evaluados.subir-resultado-archivo', $evaluado), [
            'tipo_resultado' => 'preliminar',
            'archivo'        => UploadedFile::fake()->create('informe.pdf', 500, 'application/pdf'),
        ])->assertRedirect();

        $evaluado->refresh();
        $this->assertSame('en_proceso', $evaluado->estado_evaluacion);
    }

    // ─────────────────────────────────────────────────────
    // 1c — Programar cita cambia estado a programado
    // ─────────────────────────────────────────────────────

    public function test_programar_cita_cambia_estado_programacion_a_programado(): void
    {
        // Fase 18: programar cita cambia estado_programacion, no estado_evaluacion
        $repro    = $this->crearReproConPermiso('calendario.editar');
        $sede     = Sede::factory()->create(['estado' => 1]);
        $poli     = User::factory()->create(['role_as' => 2, 'estado' => 1]);
        $orden    = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id'            => $orden->id,
            'estado_evaluacion'   => 'pendiente_de_evaluacion',
            'estado_programacion' => 'contactado',
        ]);

        $this->actingAs($repro)->post(route('calendario.programar'), [
            'evaluado_orden_id' => $evaluado->id,
            'fecha'             => now()->addDay()->format('Y-m-d'),
            'hora_inicio'       => '09:00',
            'hora_fin'          => '10:00',
            'sede_id'           => $sede->id,
            'poligrafista_id'   => $poli->id,
            'modalidad'         => 'presencial',
        ])->assertRedirect();

        $evaluado->refresh();
        $this->assertSame('programado', $evaluado->estado_programacion);
        // estado_evaluacion no cambia al programar
        $this->assertSame('pendiente_de_evaluacion', $evaluado->estado_evaluacion);
    }

    // ─────────────────────────────────────────────────────
    // 2 — Reprogramar redirige de vuelta al origen (back)
    // ─────────────────────────────────────────────────────

    public function test_programar_redirige_a_la_pagina_de_origen(): void
    {
        $repro    = $this->crearReproConPermiso('calendario.editar');
        $sede     = Sede::factory()->create(['estado' => 1]);
        $poli     = User::factory()->create(['role_as' => 2, 'estado' => 1]);
        $orden    = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id'          => $orden->id,
            'estado_evaluacion' => 'pendiente_de_evaluacion',
        ]);

        $origenUrl = route('ordenes.show', $orden);

        $this->actingAs($repro)
            ->from($origenUrl)
            ->post(route('calendario.programar'), [
                'evaluado_orden_id' => $evaluado->id,
                'fecha'             => now()->addDay()->format('Y-m-d'),
                'hora_inicio'       => '09:00',
                'hora_fin'          => '10:00',
                'sede_id'           => $sede->id,
                'poligrafista_id'   => $poli->id,
                'modalidad'         => 'presencial',
            ])->assertRedirect($origenUrl);
    }

    public function test_reprogramar_redirige_a_la_pagina_de_origen(): void
    {
        $repro    = $this->crearReproConPermiso('calendario.editar');
        $sede     = Sede::factory()->create(['estado' => 1]);
        $poli     = User::factory()->create(['role_as' => 2, 'estado' => 1]);
        $orden    = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id'            => $orden->id,
            'estado_evaluacion'   => 'pendiente_de_evaluacion',
            'estado_programacion' => 'programado',
            'fecha_programada'    => now()->addDay()->toDateTimeString(),
            'fecha_hora_fin'      => now()->addDay()->addHour()->toDateTimeString(),
            'poligrafista_id'     => $poli->id,
            'sede_id'             => $sede->id,
        ]);

        $origenUrl = route('ordenes.show', $orden);

        $this->actingAs($repro)
            ->from($origenUrl)
            ->patch(route('calendario.reprogramar', $evaluado), [
                'evaluado_orden_id' => $evaluado->id,
                'fecha'             => now()->addDays(2)->format('Y-m-d'),
                'hora_inicio'       => '10:00',
                'hora_fin'          => '11:00',
                'sede_id'           => $sede->id,
                'poligrafista_id'   => $poli->id,
                'modalidad'         => 'presencial',
            ])->assertRedirect($origenUrl);
    }
}
