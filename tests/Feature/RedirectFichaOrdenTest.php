<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

class RedirectFichaOrdenTest extends TestCase
{
    use RefreshDatabase, CreatesRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
    }

    public function test_subir_papeleria_regresa_a_la_ficha_del_evaluado(): void
    {
        Storage::fake('local');
        [$admin, $orden, $evaluado] = $this->ordenConEvaluado();

        $this->actingAs($admin)
            ->post(route('documentos-evaluado.store'), [
                'evaluado_orden_id' => $evaluado->id,
                'tipo_documento' => 'dpi_archivo',
                'archivo' => UploadedFile::fake()->create('dpi.pdf', 200, 'application/pdf'),
            ])
            ->assertRedirect(route('ordenes.show', $orden).'#evaluado-'.$evaluado->id);
    }

    public function test_cambiar_estado_regresa_a_la_ficha_del_evaluado(): void
    {
        [$admin, $orden, $evaluado] = $this->ordenConEvaluado([
            'estado_evaluacion' => 'en_proceso',
        ]);

        $this->actingAs($admin)
            ->patch(route('evaluados.cambiar-estado', $evaluado->id), [
                'tipo_estado' => 'evaluacion',
                'nuevo_estado' => 'en_revision',
            ])
            ->assertRedirect(route('ordenes.show', $orden).'#evaluado-'.$evaluado->id);
    }

    /** @return array{0: User, 1: Orden, 2: EvaluadoOrden} */
    private function ordenConEvaluado(array $extraEvaluado = []): array
    {
        $admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);
        $evaluado = EvaluadoOrden::factory()->create(array_merge([
            'orden_id' => $orden->id,
        ], $extraEvaluado));

        return [$admin, $orden, $evaluado];
    }
}
