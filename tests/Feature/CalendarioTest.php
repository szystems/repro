<?php

namespace Tests\Feature;

use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Sede;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Las fechas de los tests están hardcodeadas en marzo-2026.
        // Congelamos el reloj para que la validación "fecha no anterior a hoy" pase.
        Carbon::setTestNow(Carbon::parse('2026-03-01 08:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** Usuario REPRO (role_as = 2). */
    private function usuarioRepro(): User
    {
        return User::factory()->create(['role_as' => 2, 'estado' => 1]);
    }

    /** Usuario Admin (role_as = 3). */
    private function usuarioAdmin(): User
    {
        return User::factory()->create(['role_as' => 3, 'estado' => 1]);
    }

    /** Usuario empresa (role_as = 1). */
    private function usuarioEmpresa(): User
    {
        return User::factory()->create(['role_as' => 1, 'estado' => 1]);
    }

    /** Crear evaluado con orden asociada. */
    private function crearEvaluado(array $attrs = []): EvaluadoOrden
    {
        $orden = Orden::factory()->create();
        return EvaluadoOrden::factory()->create(array_merge(
            ['orden_id' => $orden->id],
            $attrs
        ));
    }

    // -------------------------------------------------------
    // Acceso / Autenticación
    // -------------------------------------------------------

    public function test_calendario_index_requiere_autenticacion(): void
    {
        $this->get('/calendario')->assertRedirect('/login');
    }

    public function test_calendario_dia_requiere_autenticacion(): void
    {
        $this->get('/calendario/dia/2026-03-03')->assertRedirect('/login');
    }

    public function test_calendario_accesible_para_repro(): void
    {
        $this->actingAs($this->usuarioRepro())
            ->get('/calendario')
            ->assertOk()
            ->assertViewIs('admin.calendario.index');
    }

    public function test_calendario_accesible_para_admin(): void
    {
        $this->actingAs($this->usuarioAdmin())
            ->get('/calendario')
            ->assertOk();
    }

    public function test_calendario_no_accesible_para_empresa(): void
    {
        // Empresas no pueden acceder al calendario — protegido por middleware role:admin,repro
        $this->actingAs($this->usuarioEmpresa())
            ->get('/calendario')
            ->assertForbidden();
    }

    // -------------------------------------------------------
    // Vista Mensual
    // -------------------------------------------------------

    public function test_index_muestra_mes_actual_por_defecto(): void
    {
        $this->actingAs($this->usuarioRepro())
            ->get('/calendario')
            ->assertOk()
            ->assertViewHas('mes', now()->month)
            ->assertViewHas('anio', now()->year);
    }

    public function test_index_permite_navegar_a_otro_mes(): void
    {
        $this->actingAs($this->usuarioRepro())
            ->get('/calendario?mes=6&anio=2026')
            ->assertOk()
            ->assertViewHas('mes', '6')
            ->assertViewHas('anio', '2026');
    }

    public function test_index_muestra_conteo_citas_por_dia(): void
    {
        $fecha = Carbon::parse('2026-03-15 09:00:00');
        $sede = Sede::factory()->create(['estado' => 1]);
        $poligrafista = $this->usuarioRepro();

        // Crear 2 citas el mismo día
        $this->crearEvaluado([
            'fecha_programada' => $fecha,
            'fecha_hora_fin' => $fecha->copy()->addHours(2),
            'sede_id' => $sede->id,
            'poligrafista_id' => $poligrafista->id,
            'estado_evaluacion' => 'programado',
            'tipo_servicio' => 'poligrafo',
        ]);
        $this->crearEvaluado([
            'fecha_programada' => $fecha->copy()->setTime(14, 0),
            'fecha_hora_fin' => $fecha->copy()->setTime(16, 0),
            'sede_id' => $sede->id,
            'poligrafista_id' => $poligrafista->id,
            'estado_evaluacion' => 'programado',
            'tipo_servicio' => 'vsa',
        ]);

        $this->actingAs($poligrafista)
            ->get('/calendario?mes=3&anio=2026')
            ->assertOk()
            ->assertViewHas('citasPorDia', function ($citasPorDia) {
                return isset($citasPorDia['2026-03-15']) && $citasPorDia['2026-03-15']['total'] === 2;
            });
    }

    public function test_index_filtra_por_sede(): void
    {
        $sedeA = Sede::factory()->create(['nombre' => 'Sede A', 'estado' => 1]);
        $sedeB = Sede::factory()->create(['nombre' => 'Sede B', 'estado' => 1]);
        $poligrafista = $this->usuarioRepro();
        $fecha = Carbon::parse('2026-03-10 09:00:00');

        $this->crearEvaluado([
            'fecha_programada' => $fecha,
            'fecha_hora_fin' => $fecha->copy()->addHours(2),
            'sede_id' => $sedeA->id,
            'poligrafista_id' => $poligrafista->id,
            'estado_evaluacion' => 'programado',
        ]);
        $this->crearEvaluado([
            'fecha_programada' => $fecha,
            'fecha_hora_fin' => $fecha->copy()->addHours(2),
            'sede_id' => $sedeB->id,
            'poligrafista_id' => $poligrafista->id,
            'estado_evaluacion' => 'programado',
        ]);

        $this->actingAs($poligrafista)
            ->get('/calendario?mes=3&anio=2026&sede_id=' . $sedeA->id)
            ->assertOk()
            ->assertViewHas('citasPorDia', function ($citasPorDia) {
                $total = collect($citasPorDia)->sum('total');
                return $total === 1;
            });
    }

    // -------------------------------------------------------
    // Vista Diaria
    // -------------------------------------------------------

    public function test_dia_muestra_slots_de_30_minutos(): void
    {
        $this->actingAs($this->usuarioRepro())
            ->get('/calendario/dia/2026-03-15')
            ->assertOk()
            ->assertViewIs('admin.calendario.dia')
            ->assertViewHas('slots', function ($slots) {
                // 10 horas de 8 a 18, 2 slots/hora = 20 slots
                return count($slots) === 20;
            });
    }

    public function test_dia_muestra_citas_programadas(): void
    {
        $sede = Sede::factory()->create(['estado' => 1]);
        $poligrafista = $this->usuarioRepro();

        $evaluado = $this->crearEvaluado([
            'fecha_programada' => '2026-03-15 09:00:00',
            'fecha_hora_fin' => '2026-03-15 11:00:00',
            'sede_id' => $sede->id,
            'poligrafista_id' => $poligrafista->id,
            'estado_evaluacion' => 'programado',
        ]);

        $this->actingAs($poligrafista)
            ->get('/calendario/dia/2026-03-15')
            ->assertOk()
            ->assertSee($evaluado->nombre);
    }

    public function test_dia_carga_evaluados_pendientes_para_modal(): void
    {
        $evaluadoPendiente = $this->crearEvaluado([
            'fecha_programada' => null,
            'estado_evaluacion' => 'pendiente',
        ]);

        $this->actingAs($this->usuarioRepro())
            ->get('/calendario/dia/2026-03-15')
            ->assertOk()
            ->assertViewHas('evaluadosPendientes', function ($pendientes) use ($evaluadoPendiente) {
                return $pendientes->contains('id', $evaluadoPendiente->id);
            });
    }

    public function test_dia_muestra_boton_agendar_en_slot_con_citas_de_otro_evaluador(): void
    {
        $sede = Sede::factory()->create(['estado' => 1]);
        $polA = $this->usuarioRepro();
        $fechaFutura = Carbon::now()->addDays(7)->format('Y-m-d');

        // Cita existente de polA a las 09:00
        $this->crearEvaluado([
            'fecha_programada' => $fechaFutura . ' 09:00:00',
            'fecha_hora_fin' => $fechaFutura . ' 11:00:00',
            'sede_id' => $sede->id,
            'poligrafista_id' => $polA->id,
            'estado_evaluacion' => 'programado',
        ]);

        // Sin filtro de evaluador, el botón Agendar debe seguir visible en ese slot
        $this->actingAs($polA)
            ->get('/calendario/dia/' . $fechaFutura)
            ->assertOk()
            ->assertSee('Agendar');
    }

    // -------------------------------------------------------
    // Programar Cita
    // -------------------------------------------------------

    public function test_programar_cita_exitosamente(): void
    {
        $sede = Sede::factory()->create(['estado' => 1]);
        $poligrafista = $this->usuarioRepro();
        $evaluado = $this->crearEvaluado([
            'estado_evaluacion' => 'pendiente',
            'fecha_programada' => null,
        ]);

        $this->actingAs($poligrafista)
            ->post('/calendario/programar', [
                'evaluado_orden_id' => $evaluado->id,
                'fecha' => '2026-03-20',
                'hora_inicio' => '09:00',
                'hora_fin' => '11:00',
                'sede_id' => $sede->id,
                'poligrafista_id' => $poligrafista->id,
            ])
            ->assertRedirect(route('calendario.dia', ['fecha' => '2026-03-20']));

        $evaluado->refresh();
        $this->assertEquals('programado', $evaluado->estado_evaluacion);
        $this->assertEquals('2026-03-20 09:00:00', $evaluado->fecha_programada->format('Y-m-d H:i:s'));
        $this->assertEquals('2026-03-20 11:00:00', $evaluado->fecha_hora_fin->format('Y-m-d H:i:s'));
        $this->assertEquals($sede->id, $evaluado->sede_id);
        $this->assertEquals($poligrafista->id, $evaluado->poligrafista_id);
    }

    public function test_programar_requiere_rol_repro(): void
    {
        $sede = Sede::factory()->create(['estado' => 1]);
        $empresa = $this->usuarioEmpresa();
        $evaluado = $this->crearEvaluado();

        $this->actingAs($empresa)
            ->post('/calendario/programar', [
                'evaluado_orden_id' => $evaluado->id,
                'fecha' => '2026-03-20',
                'hora_inicio' => '09:00',
                'hora_fin' => '11:00',
                'sede_id' => $sede->id,
                'poligrafista_id' => $empresa->id,
            ])
            ->assertForbidden();
    }

    public function test_programar_valida_campos_requeridos(): void
    {
        $this->actingAs($this->usuarioRepro())
            ->post('/calendario/programar', [])
            ->assertSessionHasErrors(['evaluado_orden_id', 'fecha', 'hora_inicio', 'hora_fin', 'sede_id', 'poligrafista_id']);
    }

    public function test_programar_valida_hora_fin_posterior_a_inicio(): void
    {
        $sede = Sede::factory()->create(['estado' => 1]);
        $poligrafista = $this->usuarioRepro();
        $evaluado = $this->crearEvaluado();

        $this->actingAs($poligrafista)
            ->post('/calendario/programar', [
                'evaluado_orden_id' => $evaluado->id,
                'fecha' => '2026-03-20',
                'hora_inicio' => '14:00',
                'hora_fin' => '12:00', // Fin antes que inicio
                'sede_id' => $sede->id,
                'poligrafista_id' => $poligrafista->id,
            ])
            ->assertSessionHasErrors('hora_fin');
    }

    // -------------------------------------------------------
    // Anti-traslape (CORE)
    // -------------------------------------------------------

    public function test_antitraslape_rechaza_solapamiento_mismo_poligrafista_misma_sede(): void
    {
        $sede = Sede::factory()->create(['estado' => 1]);
        $poligrafista = $this->usuarioRepro();

        // Cita existente: 9:00 - 12:00
        $this->crearEvaluado([
            'fecha_programada' => '2026-03-20 09:00:00',
            'fecha_hora_fin' => '2026-03-20 12:00:00',
            'sede_id' => $sede->id,
            'poligrafista_id' => $poligrafista->id,
            'estado_evaluacion' => 'programado',
        ]);

        $evaluado2 = $this->crearEvaluado([
            'estado_evaluacion' => 'pendiente',
            'fecha_programada' => null,
        ]);

        // Intentar agendar 10:00 - 13:00 (se cruza)
        $this->actingAs($poligrafista)
            ->post('/calendario/programar', [
                'evaluado_orden_id' => $evaluado2->id,
                'fecha' => '2026-03-20',
                'hora_inicio' => '10:00',
                'hora_fin' => '13:00',
                'sede_id' => $sede->id,
                'poligrafista_id' => $poligrafista->id,
            ])
            ->assertSessionHasErrors('traslape');

        // El evaluado no debe haber cambiado
        $evaluado2->refresh();
        $this->assertEquals('pendiente', $evaluado2->estado_evaluacion);
    }

    public function test_antitraslape_permite_citas_consecutivas(): void
    {
        $sede = Sede::factory()->create(['estado' => 1]);
        $poligrafista = $this->usuarioRepro();

        // Cita existente: 9:00 - 12:00
        $this->crearEvaluado([
            'fecha_programada' => '2026-03-20 09:00:00',
            'fecha_hora_fin' => '2026-03-20 12:00:00',
            'sede_id' => $sede->id,
            'poligrafista_id' => $poligrafista->id,
            'estado_evaluacion' => 'programado',
        ]);

        $evaluado2 = $this->crearEvaluado([
            'estado_evaluacion' => 'pendiente',
            'fecha_programada' => null,
        ]);

        // Agendar justo después: 12:00 - 14:00 (consecutiva, sin cruce)
        $this->actingAs($poligrafista)
            ->post('/calendario/programar', [
                'evaluado_orden_id' => $evaluado2->id,
                'fecha' => '2026-03-20',
                'hora_inicio' => '12:00',
                'hora_fin' => '14:00',
                'sede_id' => $sede->id,
                'poligrafista_id' => $poligrafista->id,
            ])
            ->assertRedirect();

        $evaluado2->refresh();
        $this->assertEquals('programado', $evaluado2->estado_evaluacion);
    }

    public function test_antitraslape_permite_diferente_sede(): void
    {
        $sedeA = Sede::factory()->create(['estado' => 1]);
        $sedeB = Sede::factory()->create(['estado' => 1]);
        $poligrafista = $this->usuarioRepro();

        // Cita existente en Sede A
        $this->crearEvaluado([
            'fecha_programada' => '2026-03-20 09:00:00',
            'fecha_hora_fin' => '2026-03-20 12:00:00',
            'sede_id' => $sedeA->id,
            'poligrafista_id' => $poligrafista->id,
            'estado_evaluacion' => 'programado',
        ]);

        $evaluado2 = $this->crearEvaluado([
            'estado_evaluacion' => 'pendiente',
            'fecha_programada' => null,
        ]);

        // Agendar en Sede B mismo horario → PERMITE (diferente sede)
        $this->actingAs($poligrafista)
            ->post('/calendario/programar', [
                'evaluado_orden_id' => $evaluado2->id,
                'fecha' => '2026-03-20',
                'hora_inicio' => '09:00',
                'hora_fin' => '12:00',
                'sede_id' => $sedeB->id,
                'poligrafista_id' => $poligrafista->id,
            ])
            ->assertRedirect();

        $evaluado2->refresh();
        $this->assertEquals('programado', $evaluado2->estado_evaluacion);
    }

    public function test_antitraslape_permite_diferente_poligrafista(): void
    {
        $sede = Sede::factory()->create(['estado' => 1]);
        $polA = $this->usuarioRepro();
        $polB = $this->usuarioRepro();

        // Cita existente del polA
        $this->crearEvaluado([
            'fecha_programada' => '2026-03-20 09:00:00',
            'fecha_hora_fin' => '2026-03-20 12:00:00',
            'sede_id' => $sede->id,
            'poligrafista_id' => $polA->id,
            'estado_evaluacion' => 'programado',
        ]);

        $evaluado2 = $this->crearEvaluado([
            'estado_evaluacion' => 'pendiente',
            'fecha_programada' => null,
        ]);

        // Agendar con polB mismo horario mismo sede → PERMITE (diferente poligrafista)
        $this->actingAs($polB)
            ->post('/calendario/programar', [
                'evaluado_orden_id' => $evaluado2->id,
                'fecha' => '2026-03-20',
                'hora_inicio' => '09:00',
                'hora_fin' => '12:00',
                'sede_id' => $sede->id,
                'poligrafista_id' => $polB->id,
            ])
            ->assertRedirect();

        $evaluado2->refresh();
        $this->assertEquals('programado', $evaluado2->estado_evaluacion);
    }

    public function test_antitraslape_ignora_citas_canceladas(): void
    {
        $sede = Sede::factory()->create(['estado' => 1]);
        $poligrafista = $this->usuarioRepro();

        // Cita CANCELADA existente: 9:00 - 12:00
        $this->crearEvaluado([
            'fecha_programada' => '2026-03-20 09:00:00',
            'fecha_hora_fin' => '2026-03-20 12:00:00',
            'sede_id' => $sede->id,
            'poligrafista_id' => $poligrafista->id,
            'estado_evaluacion' => 'cancelado',
        ]);

        $evaluado2 = $this->crearEvaluado([
            'estado_evaluacion' => 'pendiente',
            'fecha_programada' => null,
        ]);

        // Agendar mismo horario → PERMITE (cita anterior fue cancelada)
        $this->actingAs($poligrafista)
            ->post('/calendario/programar', [
                'evaluado_orden_id' => $evaluado2->id,
                'fecha' => '2026-03-20',
                'hora_inicio' => '09:00',
                'hora_fin' => '12:00',
                'sede_id' => $sede->id,
                'poligrafista_id' => $poligrafista->id,
            ])
            ->assertRedirect();

        $evaluado2->refresh();
        $this->assertEquals('programado', $evaluado2->estado_evaluacion);
    }

    // -------------------------------------------------------
    // Reprogramar
    // -------------------------------------------------------

    public function test_reprogramar_cita_exitosamente(): void
    {
        $sede = Sede::factory()->create(['estado' => 1]);
        $poligrafista = $this->usuarioRepro();

        $evaluado = $this->crearEvaluado([
            'fecha_programada' => '2026-03-15 09:00:00',
            'fecha_hora_fin' => '2026-03-15 11:00:00',
            'sede_id' => $sede->id,
            'poligrafista_id' => $poligrafista->id,
            'estado_evaluacion' => 'programado',
        ]);

        $this->actingAs($poligrafista)
            ->patch('/calendario/evaluados/' . $evaluado->id . '/reprogramar', [
                'evaluado_orden_id' => $evaluado->id,
                'fecha' => '2026-03-22',
                'hora_inicio' => '14:00',
                'hora_fin' => '16:00',
                'sede_id' => $sede->id,
                'poligrafista_id' => $poligrafista->id,
            ])
            ->assertRedirect(route('calendario.dia', ['fecha' => '2026-03-22']));

        $evaluado->refresh();
        $this->assertEquals('2026-03-22 14:00:00', $evaluado->fecha_programada->format('Y-m-d H:i:s'));
        $this->assertEquals('2026-03-22 16:00:00', $evaluado->fecha_hora_fin->format('Y-m-d H:i:s'));
    }

    public function test_reprogramar_excluye_evaluado_actual_de_antitraslape(): void
    {
        $sede = Sede::factory()->create(['estado' => 1]);
        $poligrafista = $this->usuarioRepro();

        $evaluado = $this->crearEvaluado([
            'fecha_programada' => '2026-03-20 09:00:00',
            'fecha_hora_fin' => '2026-03-20 11:00:00',
            'sede_id' => $sede->id,
            'poligrafista_id' => $poligrafista->id,
            'estado_evaluacion' => 'programado',
        ]);

        // Reprogramar misma fecha, ajustar solo la hora (9:30 - 11:30)
        // No debe lanzar traslape consigo mismo
        $this->actingAs($poligrafista)
            ->patch('/calendario/evaluados/' . $evaluado->id . '/reprogramar', [
                'evaluado_orden_id' => $evaluado->id,
                'fecha' => '2026-03-20',
                'hora_inicio' => '09:30',
                'hora_fin' => '11:30',
                'sede_id' => $sede->id,
                'poligrafista_id' => $poligrafista->id,
            ])
            ->assertRedirect();

        $evaluado->refresh();
        $this->assertEquals('09:30', $evaluado->fecha_programada->format('H:i'));
    }

    // -------------------------------------------------------
    // Cancelar Cita
    // -------------------------------------------------------

    public function test_cancelar_cita_exitosamente(): void
    {
        $sede = Sede::factory()->create(['estado' => 1]);
        $poligrafista = $this->usuarioRepro();

        $evaluado = $this->crearEvaluado([
            'fecha_programada' => '2026-03-20 09:00:00',
            'fecha_hora_fin' => '2026-03-20 11:00:00',
            'sede_id' => $sede->id,
            'poligrafista_id' => $poligrafista->id,
            'estado_evaluacion' => 'programado',
        ]);

        $this->actingAs($poligrafista)
            ->delete('/calendario/evaluados/' . $evaluado->id . '/cancelar')
            ->assertRedirect(route('calendario.dia', ['fecha' => '2026-03-20']));

        $evaluado->refresh();
        $this->assertEquals('cancelado', $evaluado->estado_evaluacion);
        $this->assertNull($evaluado->fecha_programada);
        $this->assertNull($evaluado->fecha_hora_fin);
    }

    // -------------------------------------------------------
    // Programar desde Orden (Modal)
    // -------------------------------------------------------

    public function test_orden_show_muestra_boton_programar_cita(): void
    {
        $poligrafista = $this->usuarioRepro();
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'estado_evaluacion' => 'pendiente',
            'fecha_programada' => null,
        ]);

        $this->actingAs($poligrafista)
            ->get('/ordenes/' . $orden->id)
            ->assertOk()
            ->assertSee('Programar cita');
    }

    public function test_orden_show_muestra_boton_reprogramar_si_ya_tiene_cita(): void
    {
        $poligrafista = $this->usuarioRepro();
        $sede = Sede::factory()->create(['estado' => 1]);
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'fecha_programada' => '2026-03-20 09:00:00',
            'fecha_hora_fin' => '2026-03-20 11:00:00',
            'sede_id' => $sede->id,
            'poligrafista_id' => $poligrafista->id,
            'estado_evaluacion' => 'programado',
        ]);

        $this->actingAs($poligrafista)
            ->get('/ordenes/' . $orden->id)
            ->assertOk()
            ->assertSee('Reprogramar');
    }

    // -------------------------------------------------------
    // Modelo: Scopes
    // -------------------------------------------------------

    public function test_scope_programados_filtra_correctamente(): void
    {
        $this->crearEvaluado([
            'fecha_programada' => '2026-03-20 09:00:00',
            'estado_evaluacion' => 'programado',
        ]);
        $this->crearEvaluado([
            'fecha_programada' => null,
            'estado_evaluacion' => 'pendiente',
        ]);
        $this->crearEvaluado([
            'fecha_programada' => '2026-03-20 09:00:00',
            'estado_evaluacion' => 'cancelado',
        ]);

        $programados = EvaluadoOrden::programados()->get();
        $this->assertCount(1, $programados);
    }

    public function test_scope_pendientes_programar(): void
    {
        $this->crearEvaluado([
            'fecha_programada' => null,
            'estado_evaluacion' => 'pendiente',
        ]);
        $this->crearEvaluado([
            'fecha_programada' => null,
            'estado_evaluacion' => 'contactado',
        ]);
        $this->crearEvaluado([
            'fecha_programada' => '2026-03-20 09:00:00',
            'estado_evaluacion' => 'programado',
        ]);

        $pendientes = EvaluadoOrden::pendientesProgramar()->get();
        $this->assertCount(2, $pendientes);
    }

    public function test_scope_en_dia(): void
    {
        $this->crearEvaluado([
            'fecha_programada' => '2026-03-20 09:00:00',
            'estado_evaluacion' => 'programado',
        ]);
        $this->crearEvaluado([
            'fecha_programada' => '2026-03-21 09:00:00',
            'estado_evaluacion' => 'programado',
        ]);

        $enDia = EvaluadoOrden::enDia('2026-03-20')->get();
        $this->assertCount(1, $enDia);
    }

    // -------------------------------------------------------
    // Modelo: Sede::tieneTraslape con rangos
    // -------------------------------------------------------

    public function test_sede_tiene_traslape_con_rango_cruzado(): void
    {
        $sede = Sede::factory()->create(['estado' => 1]);
        $poligrafista = $this->usuarioRepro();

        $this->crearEvaluado([
            'fecha_programada' => '2026-03-20 09:00:00',
            'fecha_hora_fin' => '2026-03-20 12:00:00',
            'sede_id' => $sede->id,
            'poligrafista_id' => $poligrafista->id,
            'estado_evaluacion' => 'programado',
        ]);

        // Rango que se cruza: 10:00 - 13:00
        $this->assertTrue(
            $sede->tieneTraslape($poligrafista->id, '2026-03-20 10:00:00', '2026-03-20 13:00:00')
        );

        // Rango que NO se cruza: 12:00 - 14:00 (consecutivo)
        $this->assertFalse(
            $sede->tieneTraslape($poligrafista->id, '2026-03-20 12:00:00', '2026-03-20 14:00:00')
        );
    }

    public function test_user_scope_poligrafistas(): void
    {
        User::factory()->create(['role_as' => 3, 'estado' => 1]); // admin
        User::factory()->create(['role_as' => 2, 'estado' => 1]); // repro
        User::factory()->create(['role_as' => 1, 'estado' => 1]); // empresa
        User::factory()->create(['role_as' => 2, 'estado' => 0]); // repro inactivo

        $poligrafistas = User::poligrafistas()->get();
        // Solo los 2 activos con role_as >= 2
        $this->assertCount(2, $poligrafistas);
    }
}
