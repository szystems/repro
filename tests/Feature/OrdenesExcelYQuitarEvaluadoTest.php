<?php

namespace Tests\Feature;

use App\Exports\OrdenesExport;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

class OrdenesExcelYQuitarEvaluadoTest extends TestCase
{
    use RefreshDatabase, CreatesRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
    }

    public function test_listado_muestra_boton_excel_para_admin_y_empresa(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin)
            ->get(route('ordenes.index'))
            ->assertOk()
            ->assertSee('Exportar Excel')
            ->assertSee(route('ordenes.excel'), false);

        $empresa = Empresa::factory()->create();
        $cliente = $this->crearCliente($empresa);

        $this->actingAs($cliente)
            ->get(route('ordenes.index'))
            ->assertOk()
            ->assertSee('Exportar Excel');
    }

    public function test_admin_exporta_excel_del_listado(): void
    {
        Excel::fake();

        $admin = $this->crearAdmin();
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
            'fecha_solicitud' => now(),
        ]);
        EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
        ]);

        $this->actingAs($admin)
            ->get(route('ordenes.excel'))
            ->assertOk();

        Excel::assertDownloaded(
            'listado-ordenes-'.now()->format('Y-m-d').'.xlsx',
            function (OrdenesExport $export) use ($orden) {
                return $export->collection()->contains('id', $orden->id);
            }
        );
    }

    public function test_excel_aplica_filtros_y_solo_ordenes_de_la_empresa(): void
    {
        Excel::fake();

        $empresa = Empresa::factory()->create();
        $otra = Empresa::factory()->create();
        $cliente = $this->crearCliente($empresa);

        $delMes = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $cliente->id,
            'fecha_solicitud' => now()->startOfMonth()->addDays(2),
            'estado' => 'orden_recibida',
        ]);
        $fueraDeRango = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $cliente->id,
            'fecha_solicitud' => now()->subMonths(2),
            'estado' => 'orden_recibida',
        ]);
        $ajena = Orden::factory()->create([
            'empresa_id' => $otra->id,
            'fecha_solicitud' => now(),
        ]);

        $this->actingAs($cliente)
            ->get(route('ordenes.excel', [
                'fecha_desde' => now()->startOfMonth()->toDateString(),
                'fecha_hasta' => now()->endOfMonth()->toDateString(),
            ]))
            ->assertOk();

        Excel::assertDownloaded(
            'listado-ordenes-'.now()->format('Y-m-d').'.xlsx',
            function (OrdenesExport $export) use ($delMes, $fueraDeRango, $ajena) {
                $ids = $export->collection()->pluck('id');

                return $ids->contains($delMes->id)
                    && ! $ids->contains($fueraDeRango->id)
                    && ! $ids->contains($ajena->id);
            }
        );
    }

    public function test_editar_quita_evaluado_marcado_y_deja_a_los_demas(): void
    {
        $admin = $this->crearAdmin();
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
            'estado' => 'orden_recibida',
        ]);
        $seQueda = $this->crearEvaluado($orden, '1111111111111', 'poligrafo');
        $duplicado = $this->crearEvaluado($orden, '2222222222222', 'vsa');

        $this->actingAs($admin)
            ->put(route('ordenes.update', $orden), [
                'empresa_id' => $empresa->id,
                'evaluados' => [$this->payloadEvaluado($seQueda)],
                'evaluados_eliminar' => [$duplicado->id],
            ])
            ->assertRedirect(route('ordenes.show', $orden));

        $this->assertDatabaseHas('evaluados_orden', ['id' => $seQueda->id]);
        $this->assertDatabaseMissing('evaluados_orden', ['id' => $duplicado->id]);
        $this->assertEquals(1, $orden->evaluados()->count());
    }

    public function test_editar_sin_marcar_eliminar_sigue_preservando_al_omitido(): void
    {
        $admin = $this->crearAdmin();
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
        ]);
        $uno = $this->crearEvaluado($orden, '1111111111111', 'poligrafo');
        $dos = $this->crearEvaluado($orden, '2222222222222', 'vsa');

        $this->actingAs($admin)
            ->put(route('ordenes.update', $orden), [
                'empresa_id' => $empresa->id,
                'evaluados' => [$this->payloadEvaluado($uno)],
            ])
            ->assertRedirect(route('ordenes.show', $orden));

        $this->assertDatabaseHas('evaluados_orden', ['id' => $uno->id]);
        $this->assertDatabaseHas('evaluados_orden', ['id' => $dos->id]);
    }

    public function test_no_quita_evaluado_si_ya_completo_cuestionario(): void
    {
        $admin = $this->crearAdmin();
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
        ]);
        $seQueda = $this->crearEvaluado($orden, '1111111111111', 'poligrafo');
        $conFormulario = $this->crearEvaluado($orden, '2222222222222', 'vsa', [
            'cuestionario_completado' => true,
            'estado_formulario' => 'formulario_completado_y_recibido',
        ]);

        $this->actingAs($admin)
            ->from(route('ordenes.edit', $orden))
            ->put(route('ordenes.update', $orden), [
                'empresa_id' => $empresa->id,
                'evaluados' => [$this->payloadEvaluado($seQueda)],
                'evaluados_eliminar' => [$conFormulario->id],
            ])
            ->assertRedirect(route('ordenes.edit', $orden))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('evaluados_orden', ['id' => $conFormulario->id]);
    }

    public function test_no_permite_quitar_al_unico_evaluado(): void
    {
        $admin = $this->crearAdmin();
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
        ]);
        $unico = $this->crearEvaluado($orden, '1111111111111', 'poligrafo');

        $this->actingAs($admin)
            ->from(route('ordenes.edit', $orden))
            ->put(route('ordenes.update', $orden), [
                'empresa_id' => $empresa->id,
                'evaluados' => [$this->payloadEvaluado($unico)],
                'evaluados_eliminar' => [$unico->id],
            ])
            ->assertRedirect(route('ordenes.show', $orden));

        $this->assertDatabaseHas('evaluados_orden', ['id' => $unico->id]);
    }

    public function test_cliente_puede_quitar_duplicado_en_orden_recibida(): void
    {
        $empresa = Empresa::factory()->create();
        $cliente = $this->crearCliente($empresa);
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $cliente->id,
            'estado' => 'orden_recibida',
        ]);
        $seQueda = $this->crearEvaluado($orden, '1111111111111', 'poligrafo');
        $duplicado = $this->crearEvaluado($orden, '2222222222222', 'vsa');

        $this->actingAs($cliente)
            ->put(route('ordenes.update', $orden), [
                'evaluados' => [$this->payloadEvaluado($seQueda)],
                'evaluados_eliminar' => [$duplicado->id],
            ])
            ->assertRedirect(route('ordenes.show', $orden));

        $this->assertDatabaseMissing('evaluados_orden', ['id' => $duplicado->id]);
        $this->assertDatabaseHas('evaluados_orden', ['id' => $seQueda->id]);
    }

    public function test_export_html_incluye_codigo_cuando_no_hay_xmlwriter(): void
    {
        $admin = $this->crearAdmin();
        $empresa = Empresa::factory()->create(['nombre' => 'PRUEBA EXCEL']);
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
            'codigo_orden' => 'ORD-TEST-EXCEL',
            'fecha_solicitud' => now(),
        ]);
        EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'nombre' => 'Ana',
            'apellidos' => 'Prueba',
            'tipo_servicio' => 'poligrafo',
        ]);

        $html = (new OrdenesExport(collect([$orden->fresh(['empresa', 'evaluados'])])))->toHtmlTable();

        $this->assertStringContainsString('ORD-TEST-EXCEL', $html);
        $this->assertStringContainsString('PRUEBA EXCEL', $html);
        $this->assertStringContainsString('Ana Prueba', $html);
    }

    public function test_evaluado_con_informe_no_es_eliminable(): void
    {
        $evaluado = $this->crearEvaluado(
            Orden::factory()->create(['empresa_id' => Empresa::factory()->create()->id]),
            '3333333333333',
            'poligrafo',
            ['archivo_resultado_preliminar' => 'informes/preliminar.pdf']
        );

        $this->assertFalse($evaluado->puedeEliminarseDeOrden());
        $this->assertStringContainsString('informe', $evaluado->motivoNoEliminableDeOrden());
    }

    private function crearAdmin(): User
    {
        $admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        return $admin;
    }

    private function crearCliente(Empresa $empresa): User
    {
        $cliente = User::factory()->create([
            'role_as' => 1,
            'estado' => 1,
            'principal' => 1,
            'empresa_id' => $empresa->id,
        ]);
        $cliente->roles()->attach(Role::where('name', 'empresa')->first());

        return $cliente;
    }

    private function crearEvaluado(Orden $orden, string $dpi, string $servicio, array $extra = []): EvaluadoOrden
    {
        return EvaluadoOrden::factory()->create(array_merge([
            'orden_id' => $orden->id,
            'dpi' => $dpi,
            'tipo_servicio' => $servicio,
            'tipo_formulario' => 'preempleo',
            'email' => $dpi.'@test.com',
            'cuestionario_completado' => false,
            'estado_evaluacion' => 'pendiente_de_evaluacion',
            'estado_formulario' => 'link_pendiente',
        ], $extra));
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadEvaluado(EvaluadoOrden $evaluado): array
    {
        return [
            'id' => $evaluado->id,
            'nombre' => $evaluado->nombre,
            'apellidos' => $evaluado->apellidos,
            'dpi' => $evaluado->dpi,
            'email' => $evaluado->email,
            'tipo_servicio' => $evaluado->tipo_servicio,
            'tipo_formulario' => $evaluado->tipo_formulario ?? 'preempleo',
        ];
    }
}
