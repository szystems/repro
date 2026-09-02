<?php

namespace Tests\Unit;

use App\Http\Requests\ProgramarCitaRequest;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Sede;
use App\Models\User;
use App\Support\HistorialAcademico;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/** Sprint F2 — ops/formulario (reprogramación, académico 2 niveles). */
class SprintF2UltimosCambiosTest extends TestCase
{
    use RefreshDatabase;

    public function test_niveles_academicos_visibles_universitario_son_dos(): void
    {
        $this->assertSame(
            ['universitario'],
            HistorialAcademico::nivelesVisibles('universitario')
        );
    }

    public function test_reprogramar_guarda_motivo(): void
    {
        $orden = Orden::factory()->create();
        $sede = Sede::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'sede_id' => $sede->id,
            'fecha_programada' => now()->addDay()->setTime(9, 0),
            'fecha_hora_fin' => now()->addDay()->setTime(10, 0),
            'estado_programacion' => 'programado',
        ]);

        $ok = $evaluado->reprogramarEvaluacion(
            now()->addDays(2)->setTime(11, 0)->format('Y-m-d H:i:s'),
            now()->addDays(2)->setTime(12, 0)->format('Y-m-d H:i:s'),
            null,
            $sede->id,
            'presencial',
            null,
            'Cliente solicitó cambio de horario'
        );

        $this->assertTrue($ok);
        $evaluado->refresh();
        $this->assertSame('Cliente solicitó cambio de horario', $evaluado->motivo_reprogramacion);
        $this->assertSame('reprogramado', $evaluado->estado_programacion);
    }

    public function test_motivo_reprogramacion_requerido_en_patch(): void
    {
        $admin = User::factory()->create(['role_as' => 2]);
        $this->actingAs($admin);

        $request = ProgramarCitaRequest::create('/calendario/evaluados/1/reprogramar', 'PATCH', [
            'fecha' => now()->addDay()->format('Y-m-d'),
            'hora_inicio' => '09:00',
            'hora_fin' => '10:00',
            'sede_id' => 1,
        ]);
        $request->setContainer(app())->setRedirector(app('redirect'));
        $request->setUserResolver(fn () => $admin);

        $validator = Validator::make($request->all(), $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('motivo_reprogramacion', $validator->errors()->toArray());
    }
}
